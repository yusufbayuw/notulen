<?php

namespace App\Http\Controllers;

use App\Jobs\DispatchTranscription;
use App\Models\Meeting;
use App\Models\Organization;
use App\Models\ProcessingJob;
use App\Models\Recording;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RecordingController
{
    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'language' => ['required', 'in:id,en'],
            'audio' => ['required', 'file', 'max:'.config('notulen.max_upload_kilobytes'),
                'mimes:mp3,wav,m4a,mp4,ogg,webm'],
        ]);

        $file = $request->file('audio');
        if (! $file || ! $file->isValid()) {
            throw ValidationException::withMessages(['audio' => 'File audio tidak valid.']);
        }

        $storedPath = null;
        try {
            $meeting = DB::transaction(function () use ($file, $data, $organization, $request, &$storedPath): Meeting {
                $meeting = Meeting::create([
                    'organization_id' => $organization->id,
                    'created_by' => $request->user()->id,
                    'title' => $data['title'],
                    'language' => $data['language'],
                    'status' => 'queued',
                ]);
                $directory = 'organizations/'.$organization->id.'/meetings/'.$meeting->id;
                $storedPath = $file->storeAs($directory, Str::uuid().'.'.$file->extension(), 'local');
                if (! $storedPath) {
                    throw new \RuntimeException('Failed to store recording.');
                }
                $recording = Recording::create([
                    'organization_id' => $organization->id,
                    'meeting_id' => $meeting->id,
                    'object_path' => $storedPath,
                    'original_filename' => Str::limit(basename($file->getClientOriginalName()), 250, ''),
                    'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                    'size_bytes' => $file->getSize(),
                    'sha256' => hash_file('sha256', $file->getRealPath()),
                ]);
                $job = ProcessingJob::create([
                    'organization_id' => $organization->id,
                    'meeting_id' => $meeting->id,
                    'recording_id' => $recording->id,
                    'type' => 'transcribe',
                    'status' => 'queued',
                ]);
                DispatchTranscription::dispatch($job->id)->afterCommit();

                return $meeting;
            });
        } catch (\Throwable $e) {
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }
            throw $e;
        }

        return redirect()->route('workspace.meetings.show', [$organization, $meeting]);
    }
}
