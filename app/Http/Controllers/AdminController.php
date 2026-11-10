<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('month');
        $year = $request->query('year');

        $claims = Claim::with(['items', 'attachments', 'user'])
            ->forPeriod($month, $year)
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        // Compact payload for the Alpine review modal.
        $payload = $claims->getCollection()->map(fn (Claim $c) => [
            'id' => $c->id,
            'claim_number' => $c->claim_number,
            'period' => strtoupper(\Carbon\Carbon::create($c->claim_year, $c->claim_month, 1)->format('F Y')),
            'status' => $c->status,
            'rejection_reason' => $c->rejection_reason,
            'full_name' => $c->full_name,
            'designation' => $c->designation,
            'department' => $c->department,
            'staff_no' => $c->staff_no,
            'bank_account' => $c->bank_account,
            'vehicle_reg_no' => $c->vehicle_reg_no,
            'vehicle_type' => $c->vehicle_type,
            'vehicle_model' => $c->vehicle_model,
            'vehicle_capacity' => $c->vehicle_capacity,
            'vehicle_ownership' => $c->vehicle_ownership,
            'total_amount' => (float) $c->total_amount,
            'items' => $c->items->mapWithKeys(fn ($i) => [
                $i->item_key => ['unit_input' => (float) $i->unit_input, 'amount' => (float) $i->amount],
            ]),
            'attachments' => $c->attachments->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->original_name,
                'type' => $a->file_type,
                'url' => route('claims.attachment', $a),
            ]),
        ])->values();

        $years = Claim::select('claim_year')->distinct()->orderByDesc('claim_year')->pluck('claim_year');
        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('admin.dashboard', [
            'claims' => $claims,
            'payload' => $payload,
            'years' => $years,
            'month' => $month,
            'year' => $year,
            'rows' => config('claim_rates.rows'),
        ]);
    }

    public function updateStatus(Request $request, Claim $claim)
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($claim->status !== 'pending') {
            return back()->withErrors(['status' => "Claim #{$claim->claim_number} has already been {$claim->status}."]);
        }

        $claim->forceFill([
            'status' => $data['status'],
            'rejection_reason' => $data['status'] === 'rejected' ? ($data['rejection_reason'] ?? null) : null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ])->save();

        return back()->with('status', "Claim #{$claim->claim_number} {$data['status']}.");
    }
}
