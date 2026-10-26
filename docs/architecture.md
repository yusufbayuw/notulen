# Architecture — implementation status

## Implemented in the bootstrap PR

- **Backend**: Laravel 13, session auth, workspace registration, membership, tenant-scoped routes, SQL records.
- **UI**: Vue 3 + Inertia, login/register, upload and meeting detail.
- **Media**: Laravel private local disk; per-workspace storage path; authenticated audio stream.
- **AI**: signed Laravel -> FastAPI dispatch; Redis RQ; local ffmpeg + faster-whisper; optional Ollama JSON summary; signed FastAPI -> Laravel callback.
- **CI**: focused Laravel feature tests + Python tests, Vite build.

## Request lifecycle

1. Authenticated workspace member uploads file (100 MB limit).
2. Laravel writes to a private disk path beginning with the **trusted** organization ID and creates a queued processing record.
3. Laravel Redis worker submits a signed job to internal FastAPI.
4. FastAPI validates HMAC timestamp and deduplicates intake using Redis NX.
5. Python RQ worker validates media path, checks duration (2h max), runs ffmpeg and faster-whisper.
6. If configured, worker calls local Ollama for hierarchical meeting summary. If unavailable, transcript is returned **without** summary.
7. Python sends signed callback. Laravel locks the processing row, verifies tenant ID and writes results once.
8. Workspace can view synchronized audio and transcript.

## Local development

- Root `.env` controls **Compose interpolation**.
- `apps/web/.env` controls **Laravel configuration**. Compose overrides DB_PASSWORD and AI_INTERNAL_SECRET to ensure the two services match.
- Run `docker compose up --build -d` after configuring env files, followed by `docker compose exec web php artisan migrate --force`.
- To enable summary: `docker compose --profile llm up -d ollama`, then `docker compose exec ollama ollama pull qwen3:4b`. Configure `OLLAMA_MODEL=qwen3:4b` in root .env and recreate `ai-worker`.
- The initial Whisper model is `small` for CPU feasibility. Benchmark `large-v3` with GPU separately before asserting production Indonesian-language accuracy.
- Containers take time to download their model weights on first use; pin/cache model revisions for production.

## Explicitly **not implemented**

- Subscription/billing, quotas/reservations, email verification and account recovery, speaker diarization, collaborative editor, export DOCX/PDF, realtime transcription, meeting bots, native mobile app.
- PostgreSQL or MariaDB scale-out, S3 storage, signed multipart uploads, live metering, antivirus, detailed rate/concurrency controls and admin audit trails.
- At-rest encryption and backup restore testing, evidence-linked action items, production-grade observability.

The scaffold is for a development pilot **only**, not a commercial production launch.
