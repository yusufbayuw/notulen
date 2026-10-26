<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingSummary extends Model
{
    protected $fillable = ['organization_id', 'meeting_id', 'content', 'model_name'];

    protected function casts(): array
    {
        return ['content' => 'array'];
    }
}
