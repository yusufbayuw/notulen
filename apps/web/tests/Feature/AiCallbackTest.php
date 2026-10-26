<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\Organization;
use App\Models\ProcessingJob;
use App\Models\Recording;
use App\Models\TranscriptSegment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiCallbackTest extends TestCase
{
    use RefreshDatabase;

    private function aiJob(): ProcessingJob
    {
        $u = User::create(['name' => 'Owner', 'email' => 'owner@example.test', 'password' => 'secure-password-123']);
        $org = Organization::create(['name' => 'Test', 'slug' => 'test', 'owner_id' => $u->id]);
        $meeting = Meeting::create(['organization_id' => $org->id, 'created_by' => $u->id, 'title' => 'Test', 'status' => 'queued']);
        $recording = Recording::create([
            'organization_id' => $org->id, 'meeting_id' => $meeting->id,
            'object_path' => 'organizations/1/meetings/1/123e4567-e89b-12d3-a456-426614174000.mp3',
            'original_filename' => 'meeting.mp3', 'mime_type' => 'audio/mpeg',
            'size_bytes' => 100, 'sha256' => str_repeat('a', 64),
        ]);

        return ProcessingJob::create([
            'organization_id' => $org->id, 'meeting_id' => $meeting->id,
            'recording_id' => $recording->id, 'status' => 'queued', 'type' => 'transcribe',
        ]);
    }

    public function test_signed_callback_is_idempotent(): void
    {
        config()->set('notulen.ai_internal_secret', str_repeat('x', 40));
        $job = $this->aiJob();
        $payload = [
            'job_id' => $job->id, 'organization_id' => $job->organization_id,
            'status' => 'completed', 'segments' => [
                ['start_ms' => 0, 'end_ms' => 1500, 'text' => 'Selamat pagi.', 'speaker_label' => null],
            ],
            'summary' => ['overview' => 'Pembukaan rapat.', 'decisions' => [], 'action_items' => []],
            'model_name' => 'test-local',
        ];
        $body = json_encode($payload);
        $ts = (string) time();
        $headers = [
            'X-Timestamp' => $ts,
            'X-Signature' => hash_hmac('sha256', $ts.'.'.$body, str_repeat('x', 40)),
        ];

        $this->postJson('/api/internal/ai/callback', $payload, $headers)->assertOk();
        $this->postJson('/api/internal/ai/callback', $payload, $headers)->assertOk();
        $this->assertSame(1, TranscriptSegment::count());
        $this->assertSame('completed', $job->fresh()->status);
    }

    public function test_unsigned_callback_is_rejected(): void
    {
        config()->set('notulen.ai_internal_secret', str_repeat('x', 40));
        $job = $this->aiJob();
        $this->postJson('/api/internal/ai/callback', [
            'job_id' => $job->id, 'organization_id' => $job->organization_id,
            'status' => 'failed',
        ])->assertUnauthorized();
        $this->assertSame('queued', $job->fresh()->status);
    }

    public function test_valid_signature_cannot_change_tenant(): void
    {
        config()->set('notulen.ai_internal_secret', str_repeat('x', 40));
        $job = $this->aiJob();
        $payload = [
            'job_id' => $job->id, 'organization_id' => $job->organization_id + 1,
            'status' => 'failed',
        ];
        $body = json_encode($payload);
        $ts = (string) time();
        $headers = [
            'X-Timestamp' => $ts,
            'X-Signature' => hash_hmac('sha256', $ts.'.'.$body, str_repeat('x', 40)),
        ];
        $this->postJson('/api/internal/ai/callback', $payload, $headers)->assertNotFound();
        $this->assertSame('queued', $job->fresh()->status);
    }
}
