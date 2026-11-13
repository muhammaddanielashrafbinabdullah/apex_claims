<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ClaimItem;
use Illuminate\Http\JsonResponse;
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

        // Compact payload for the Alpine review pop-up.
        $payload = $claims->getCollection()->map(fn (Claim $c) => self::claimPayload($c))->values();

        // Count cards (follow the Month / Year filters).
        $counts = Claim::forPeriod($month, $year)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $stats = [
            'submitted' => (int) $counts->sum(),
            'pending' => (int) ($counts['pending'] ?? 0),
            'approved' => (int) ($counts['approved'] ?? 0),
            'rejected' => (int) ($counts['rejected'] ?? 0),
        ];

        $years = Claim::select('claim_year')->distinct()->orderByDesc('claim_year')->pluck('claim_year');
        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('admin.dashboard', [
            'claims' => $claims,
            'payload' => $payload,
            'stats' => $stats,
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

        if ($data['status'] === 'approved') {
            // Approving needs at least one valid (not disallowed) line.
            if ($claim->items()->where('disallowed', false)->doesntExist()) {
                return back()->withErrors(['status' => 'Every line of this claim is declined. Reject the claim instead.']);
            }
            $claim->recalculateTotal();
        }

        $claim->forceFill([
            'status' => $data['status'],
            'rejection_reason' => $data['status'] === 'rejected' ? ($data['rejection_reason'] ?? null) : null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ])->save();

        return back()->with('status', "Claim #{$claim->claim_number} {$data['status']}.");
    }

    /** Disallow one line of a pending claim. The claim total is recalculated from the valid lines. */
    public function disallowItem(Request $request, Claim $claim, ClaimItem $item): JsonResponse
    {
        $this->guardItem($claim, $item);

        $data = $request->validate(['reason' => ['required', 'string', 'max:500']], ['reason.required' => 'Please enter a reason for declining.']);

        $item->forceFill([
            'disallowed' => true,
            'disallow_reason' => $data['reason'] ?? null,
            'disallowed_by' => $request->user()->id,
            'disallowed_at' => now(),
        ])->save();

        return $this->respond($claim);
    }

    /** Bring a disallowed line back (only while the claim is still pending). */
    public function restoreItem(Request $request, Claim $claim, ClaimItem $item): JsonResponse
    {
        $this->guardItem($claim, $item);

        $item->forceFill([
            'disallowed' => false,
            'disallow_reason' => null,
            'disallowed_by' => null,
            'disallowed_at' => null,
        ])->save();

        return $this->respond($claim);
    }

    private function guardItem(Claim $claim, ClaimItem $item): void
    {
        abort_unless($item->claim_id === $claim->id, 404);
        abort_if($claim->status !== 'pending', 422, 'Only pending claims can be changed.');
    }

    private function respond(Claim $claim): JsonResponse
    {
        $claim->recalculateTotal();
        $claim->load(['items', 'attachments']);

        return response()->json(['claim' => self::claimPayload($claim)]);
    }

    public static function claimPayload(Claim $c): array
    {
        return $c->reviewPayload(false);
    }
}
