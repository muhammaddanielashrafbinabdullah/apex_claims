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

    public function getCategoriesListAttribute(): string
    {
        return $this->items->pluck('category')->unique()->implode(', ');
    }
}
