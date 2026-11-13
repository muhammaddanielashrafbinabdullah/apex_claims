<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ClaimAttachment;
use App\Services\ClaimCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClaimController extends Controller
{
    public function create(Request $request)
    {
        // Heading "CLAIM OF <MONTH YEAR>" follows the calendar (Malaysia time) on every page load.
        return view('claims.create', [
            'period' => strtoupper(now(config('claim_rates.timezone'))->format('F Y')),
        ]);
    }

    public function store(Request $request, ClaimCalculator $calculator)
    {
        $rules = [
            'full_name' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'staff_no' => ['required', 'string', 'max:50'],
            'bank_account' => ['required', 'string', 'max:255'],
            'vehicle_reg_no' => ['nullable', 'string', 'max:30'],
            'vehicle_type' => ['nullable', 'string', 'max:50'],
            'vehicle_model' => ['nullable', 'string', 'max:100'],
            'vehicle_capacity' => ['nullable', 'string', 'max:50'],
            'vehicle_ownership' => ['nullable', 'string', 'max:50'],

            'items' => ['required', 'array'],

            // Receipts/proof are optional (any number of files, 10 MB each).
            'attachments' => ['nullable', 'array'],
            'attachments.*' => [
                'file',
                'mimes:'.implode(',', config('claim_rates.allowed_mimes')),
                'mimetypes:application/pdf,image/jpeg',
                'max:'.config('claim_rates.max_file_kb'),
            ],
        ];

        // Per-row numeric bounds generated from the rate matrix.
        foreach (config('claim_rates.rows') as $row) {
            $rules['items.'.$row['key']] = ['nullable', 'numeric', 'min:0', 'max:'.$row['max']];
        }

        $data = $request->validate($rules, [
            'attachments.*.mimes' => 'Attachments must be PDF or JPG files.',
            'attachments.*.mimetypes' => 'Attachments must be PDF or JPG files.',
        ]);

        // Recalculate server-side: any total sent by the browser is ignored.
        $result = $calculator->calculate($data['items']);
        if ($result['total'] <= 0) {
            throw ValidationException::withMessages(['items' => 'Enter at least one claim item with a value above zero.']);
        }

        $storedPaths = [];

        try {
            $claim = DB::transaction(function () use ($request, $data, $result, &$storedPaths) {
                $now = now();
                $local = now(config('claim_rates.timezone')); // claim month/year follow Malaysia time

                $claim = Claim::create([
                    'user_id' => $request->user()->id,
                    'full_name' => $data['full_name'],
                    'designation' => $data['designation'] ?? null,
                    'department' => $data['department'] ?? null,
                    'staff_no' => $data['staff_no'],
                    'bank_account' => $data['bank_account'],
                    'vehicle_reg_no' => $data['vehicle_reg_no'] ?? null,
                    'vehicle_type' => $data['vehicle_type'] ?? null,
                    'vehicle_model' => $data['vehicle_model'] ?? null,
                    'vehicle_capacity' => $data['vehicle_capacity'] ?? null,
                    'vehicle_ownership' => $data['vehicle_ownership'] ?? null,
                    'total_amount' => $result['total'],
                    'submitted_at' => $now,
                    'claim_month' => $local->month,
                    'claim_year' => $local->year,
                ]);

                $claim->items()->createMany($result['lines']);

                foreach ($request->file('attachments', []) as $file) {
                    // Random server-side name; the client filename is only stored as display text.
                    $ext = $file->guessExtension() === 'pdf' ? 'pdf' : 'jpg';
                    $path = $file->storeAs("claims/{$claim->id}", Str::uuid().'.'.$ext, 'local');
                    $storedPaths[] = $path;

                    $claim->attachments()->create([
                        'file_path' => $path,
                        'original_name' => Str::limit(basename($file->getClientOriginalName()), 200, ''),
                        'file_type' => $ext,
                        'file_size' => $file->getSize(),
                    ]);
                }

                return $claim;
            });
        } catch (\Throwable $e) {
            // Roll back files too if the DB transaction failed.
            Storage::disk('local')->delete($storedPaths);
            report($e);

            return back()->withInput()->withErrors(['submit' => 'Could not save your claim. Please try again.']);
        }

        return redirect()->route('claims.history')
            ->with('status', "Claim #{$claim->claim_number} submitted successfully.");
    }

    public function history(Request $request)
    {
        $user = $request->user();

        $claims = $user->claims()
            ->with(['items', 'attachments'])
            ->forPeriod($request->query('month'), $request->query('year'))
            ->latest('submitted_at')
            ->paginate(10)
            ->withQueryString();

        // Compact data for the read-only claim pop-up (disallow marks only on approved claims).
        $payload = $claims->getCollection()->map(fn ($c) => $c->reviewPayload(true))->values();

        $years = $user->claims()->select('claim_year')->distinct()->orderByDesc('claim_year')->pluck('claim_year');
        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('claims.history', [
            'claims' => $claims,
            'payload' => $payload,
            'years' => $years,
            'rows' => config('claim_rates.rows'),
        ]);
    }

    /** Authorised download/preview of a private attachment (owner or admin). */
    public function attachment(Request $request, ClaimAttachment $attachment)
    {
        $claim = $attachment->claim;
        abort_unless($request->user()->isAdmin() || $claim->user_id === $request->user()->id, 403);
        abort_unless(Storage::disk('local')->exists($attachment->file_path), 404);

        return response()->file(Storage::disk('local')->path($attachment->file_path), [
            'Content-Type' => $attachment->file_type === 'pdf' ? 'application/pdf' : 'image/jpeg',
            'Content-Disposition' => 'inline; filename="'.addslashes($attachment->original_name).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
