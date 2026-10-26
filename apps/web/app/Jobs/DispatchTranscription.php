<?php

namespace App\Jobs;

use App\Models\ProcessingJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class DispatchTranscription implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $processingJobId)
    {
    }

    public function handle(): void
    {
        $job = ProcessingJob::query()->with(['meeting', 'recording'])->findOrFail($this->processingJobId);
        if (! in_array($job->status, ['queued', 'dispatching'], true)) {
            return;
        }

        $secret = (string) config('notulen.ai_internal_secret');
        if (strlen($secret) < 32) {
            throw new RuntimeException('AI_INTERNAL_SECRET must be configured securely.');
        }
        $payload = json_encode([
            'job_id' => $job->id,
            'organization_id' => $job->organization_id,
            'meeting_id' => $job->meeting_id,
            'storage_path' => $job->recording->object_path,
            'language' => $job->meeting->language,
        ], JSON_THROW_ON_ERROR);

        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
        Http::timeout(config('notulen.ai_request_timeout'))
            ->withHeaders(['X-Timestamp' => $timestamp, 'X-Signature' => $signature])
            ->withBody($payload, 'application/json')
            ->post(rtrim(config('notulen.ai_service_url'), '/').'/v1/jobs')
            ->throw();
    }

    public function failed(?Throwable $exception): void
    {
        $job = ProcessingJob::find($this->processingJobId);
        if ($job && in_array($job->status, ['queued', 'dispatching'], true)) {
            $job->update(['status' => 'failed', 'error_code' => 'dispatch_failed']);
            $job->meeting()->where('status', 'queued')->update(['status' => 'failed']);
        }
    }
}
