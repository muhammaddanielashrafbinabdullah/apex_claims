<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One receipt of a claim line (amount + its own file(s)). */
class ClaimItemEntry extends Model
{
    protected $fillable = ['claim_item_id', 'amount', 'sort_order'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ClaimItem::class, 'claim_item_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ClaimAttachment::class, 'entry_id');
    }
}
