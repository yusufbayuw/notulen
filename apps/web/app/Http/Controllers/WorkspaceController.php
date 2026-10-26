<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceController
{
    public function dashboard(Request $request, Organization $organization): Response
    {
        $meetings = Meeting::query()->where('organization_id', $organization->id)
            ->latest('id')->limit(30)->get(['id', 'title', 'status', 'language', 'created_at']);

        return Inertia::render('Workspace/Dashboard', [
            'workspace' => $organization->only('id', 'name', 'slug'),
            'meetings' => $meetings,
            'currentUser' => $request->user()->only('id', 'name', 'email'),
        ]);
    }

    public function showMeeting(Organization $organization, int $meeting): Response
    {
        $record = Meeting::query()->where('organization_id', $organization->id)
            ->whereKey($meeting)->firstOrFail();
        $record->load(['segments', 'summary']);

        return Inertia::render('Workspace/Meeting', [
            'workspace' => $organization->only('name', 'slug'),
            'meeting' => $record->only('id', 'title', 'status', 'language', 'created_at'),
            'segments' => $record->segments->map->only('id', 'start_ms', 'end_ms', 'speaker_label', 'text'),
            'summary' => $record->summary?->content,
        ]);
    }
}
