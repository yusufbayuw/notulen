<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessingJob extends Model
{
    protected $fillable = ['organization_id', 'meeting_id', 'recording_id', 'type', 'status', 'error_code'];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function recording(): BelongsTo
    {
        return $this->belongsTo(Recording::class);
    }
}
