<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimAttachment extends Model
{
    protected $fillable = ['claim_id', 'file_path', 'original_name', 'file_type', 'file_size'];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }
}
