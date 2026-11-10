<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimItem extends Model
{
    protected $fillable = ['claim_id', 'item_key', 'category', 'description', 'unit_input', 'amount'];

    protected function casts(): array
    {
        return [
            'unit_input' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }
}
