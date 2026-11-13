<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimItem extends Model
{
    // The disallow_* fields are NOT mass-assignable: only AdminController sets them (forceFill).
    protected $fillable = ['claim_id', 'item_key', 'category', 'description', 'unit_input', 'amount'];

    protected function casts(): array
    {
        return [
            'unit_input' => 'decimal:2',
            'amount' => 'decimal:2',
            'disallowed' => 'boolean',
            'disallowed_at' => 'datetime',
        ];
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }
}
