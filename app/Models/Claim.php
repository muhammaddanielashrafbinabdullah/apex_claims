<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Claim extends Model
{
    public const STATUSES = ['pending', 'approved', 'rejected'];

    protected $fillable = [
        'user_id',
        'full_name', 'designation', 'department', 'staff_no', 'bank_account',
        'vehicle_reg_no', 'vehicle_type', 'vehicle_model', 'vehicle_capacity', 'vehicle_ownership',
        'total_amount', 'submitted_at', 'claim_month', 'claim_year',
    ];

    // status / rejection_reason / reviewed_* are deliberately NOT in $fillable:
    // only AdminController sets them, via forceFill().

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ClaimItem::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ClaimAttachment::class);
    }

    public function scopeForPeriod(Builder $query, $month, $year): Builder
    {
        return $query
            ->when($month, fn ($q) => $q->where('claim_month', $month))
            ->when($year, fn ($q) => $q->where('claim_year', $year));
    }

    public function getClaimNumberAttribute(): string
    {
        return str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    // Categories of the valid (not disallowed) lines only.
    public function getCategoriesListAttribute(): string
    {
        return $this->items->where('disallowed', false)->pluck('category')->unique()->implode(', ');
    }

    // Lines the admin disallowed. Staff only see them once the claim has been APPROVED.
    public function getDisallowedLinesAttribute()
    {
        if ($this->status !== 'approved') {
            return collect();
        }

        return $this->items->where('disallowed', true)->values();
    }

    // What staff see as the total: approved claims show the approved (valid) total, others the full claimed amount.
    public function getStaffTotalAttribute(): float
    {
        return $this->status === 'approved' ? (float) $this->total_amount : $this->claimed_total;
    }

    // Categories staff see: everything they claimed.
    public function getStaffCategoriesAttribute(): string
    {
        return $this->items->pluck('category')->unique()->implode(', ');
    }

    // Sum of every line as originally claimed.
    public function getClaimedTotalAttribute(): float
    {
        return round((float) $this->items->sum('amount'), 2);
    }

    // total_amount always equals the sum of the valid (not disallowed) lines.
    public function recalculateTotal(): void
    {
        $this->total_amount = round((float) $this->items()->where('disallowed', false)->sum('amount'), 2);
        $this->save();
    }

    /**
     * Data for the read-only claim pop-up (admin review and staff history).
     * Staff never see disallow marks unless the claim is approved.
     */
    public function reviewPayload(bool $forStaff = false): array
    {
        $showMarks = ! $forStaff || $this->status === 'approved';

        return [
            'id' => $this->id,
            'claim_number' => $this->claim_number,
            'period' => strtoupper(\Carbon\Carbon::create($this->claim_year, $this->claim_month, 1)->format('F Y')),
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'full_name' => $this->full_name,
            'designation' => $this->designation,
            'department' => $this->department,
            'staff_no' => $this->staff_no,
            'bank_account' => $this->bank_account,
            'vehicle_reg_no' => $this->vehicle_reg_no,
            'vehicle_type' => $this->vehicle_type,
            'vehicle_model' => $this->vehicle_model,
            'vehicle_capacity' => $this->vehicle_capacity,
            'vehicle_ownership' => $this->vehicle_ownership,
            'total_amount' => $forStaff ? $this->staff_total : (float) $this->total_amount,
            'claimed_total' => $this->claimed_total,
            'items' => $this->items->mapWithKeys(fn ($i) => [
                $i->item_key => [
                    'id' => $i->id,
                    'unit_input' => (float) $i->unit_input,
                    'amount' => (float) $i->amount,
                    'disallowed' => $showMarks && (bool) $i->disallowed,
                    'disallow_reason' => $showMarks ? $i->disallow_reason : null,
                ],
            ]),
            'attachments' => $this->attachments->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->original_name,
                'type' => $a->file_type,
                'url' => route('claims.attachment', $a),
            ]),
        ];
    }
}
