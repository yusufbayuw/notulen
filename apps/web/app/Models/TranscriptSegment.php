<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranscriptSegment extends Model
{
    protected $fillable = ['organization_id', 'meeting_id', 'position', 'start_ms', 'end_ms', 'speaker_label', 'text'];
}
