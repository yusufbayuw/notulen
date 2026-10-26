<?php

namespace Tests\Feature;

use App\Jobs\DispatchTranscription;
use App\Models\Organization;
use App\Models\Recording;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_member_can_upload_to_private_storage_and_queue_ai(): void
    {
        Storage::fake('local');
        Queue::fake();
        $user = User::create(['name' => 'Owner', 'email' => 'owner@example.test', 'password' => 'long-secure-password']);
        $org = Organization::create(['owner_id' => $user->id, 'name' => 'Organisasi', 'slug' => 'org']);
        $org->users()->attach($user->id, ['role' => 'owner']);

        $this->actingAs($user)->post('/w/org/recordings', [
            'title' => 'Rapat Mingguan',
            'language' => 'id',
            'audio' => UploadedFile::fake()->create('meeting.mp3', 100, 'audio/mpeg'),
        ])->assertRedirect();

        $recording = Recording::firstOrFail();
        $this->assertStringStartsWith('organizations/'.$org->id.'/meetings/', $recording->object_path);
        Storage::disk('local')->assertExists($recording->object_path);
        Queue::assertPushed(DispatchTranscription::class);
    }
}
