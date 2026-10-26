<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Meeting extends Model
{
    protected $fillable = ['organization_id', 'created_by', 'title', 'language', 'status'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function recordings(): HasMany
    {
        return $this->hasMany(Recording::class);
    }

    public function segments(): HasMany
    {
        return $this->hasMany(TranscriptSegment::class)->orderBy('position');
    }

    public function summary(): HasOne
    {
        return $this->hasOne(MeetingSummary::class);
    }
}
