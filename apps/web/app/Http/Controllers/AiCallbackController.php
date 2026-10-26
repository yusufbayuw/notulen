<?php

namespace App\Http\Controllers;

use App\Models\ProcessingJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiCallbackController
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = (string) config('notulen.ai_internal_secret');
        $timestamp = (string) $request->header('X-Timestamp', '');
        $signature = (string) $request->header('X-Signature', '');
        if (strlen($secret) < 32 || ! ctype_digit($timestamp) ||
            abs(time() - (int) $timestamp) > 300 ||
            ! hash_equals(hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret), $signature)) {
            abort(401);
        }

        $data = $request->validate([
            'job_id' => ['required', 'integer'],
            'organization_id' => ['required', 'integer'],
            'status' => ['required', 'in:completed,failed'],
            'error_code' => ['nullable', 'string', 'max:80'],
            'segments' => ['required_if:status,completed', 'array', 'max:5000'],
            'segments.*.start_ms' => ['required', 'integer', 'min:0'],
            'segments.*.end_ms' => ['required', 'integer', 'min:0'],
            'segments.*.text' => ['required', 'string', 'max:5000'],
            'segments.*.speaker_label' => ['nullable', 'string', 'max:60'],
            'summary' => ['nullable', 'array'],
            'summary.overview' => ['nullable', 'string', 'max:30000'],
            'summary.decisions' => ['nullable', 'array', 'max:100'],
            'summary.action_items' => ['nullable', 'array', 'max:100'],
            'model_name' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($data): void {
            $job = ProcessingJob::query()->lockForUpdate()->findOrFail($data['job_id']);
            abort_unless((int) $data['organization_id'] === (int) $job->organization_id, 404);

            if (in_array($job->status, ['completed', 'failed'], true)) {
                return; // Idempotent: callbacks must not duplicate transcripts.
            }

            if ($data['status'] === 'failed') {
                $job->update(['status' => 'failed', 'error_code' => $data['error_code'] ?? 'ai_failed']);
                $job->meeting()->update(['status' => 'failed']);
                return;
            }

            $meeting = $job->meeting;
            foreach ($data['segments'] as $position => $segment) {
                abort_if($segment['end_ms'] < $segment['start_ms'], 422);
                $meeting->segments()->create([
                    'organization_id' => $job->organization_id,
                    'position' => $position,
                    'start_ms' => $segment['start_ms'],
                    'end_ms' => $segment['end_ms'],
                    'speaker_label' => $segment['speaker_label'] ?? null,
                    'text' => $segment['text'],
                ]);
            }
            if (isset($data['summary'])) {
                $meeting->summary()->create([
                    'organization_id' => $job->organization_id,
                    'content' => $data['summary'],
                    'model_name' => $data['model_name'] ?? null,
                ]);
            }
            $job->update(['status' => 'completed']);
            $meeting->update(['status' => 'completed']);
        });

        return response()->json(['ok' => true]);
    }
}
