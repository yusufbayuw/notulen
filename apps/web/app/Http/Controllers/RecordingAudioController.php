<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Organization;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecordingAudioController
{
    public function __invoke(Organization $organization, int $meeting): StreamedResponse
    {
        $record = Meeting::query()->where('organization_id', $organization->id)
            ->whereKey($meeting)->firstOrFail();
        $recording = $record->recordings()->firstOrFail();

        abort_unless(Storage::disk('local')->exists($recording->object_path), 404);

        return Storage::disk('local')->response(
            $recording->object_path,
            $recording->original_filename,
            ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'],
            'inline'
        );
    }
}
