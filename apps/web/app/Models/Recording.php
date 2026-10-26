<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recording extends Model
{
    protected $fillable = ['organization_id', 'meeting_id', 'object_path', 'original_filename', 'mime_type', 'size_bytes', 'sha256'];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }
}
