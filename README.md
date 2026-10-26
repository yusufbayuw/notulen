# Notulen AI

Indonesian-first, self-hostable meeting transcription and notetaking SaaS. **Early implementation scaffold — not production-ready.**

## Architecture

- `apps/web`: Laravel 13 + Vue 3/Inertia frontend, workspace-scoped APIs and records
- `services/ai`: Python FastAPI + RQ worker, local faster-whisper speech recognition, optional local Ollama summarization
- `compose.yaml`: local development stack (MariaDB, Redis, Laravel, worker and AI services)

## Quick start (development)

1. Copy `.env.example` to `.env`, set unique `DB_PASSWORD` and `DB_ROOT_PASSWORD`.
2. Copy `apps/web/.env.example` to `apps/web/.env`. Set matching database password and generate a **new** `APP_KEY`, e.g. `docker compose run --rm web php artisan key:generate` after the images build. Set the same long random `AI_INTERNAL_SECRET` for both Laravel and Python through the root Compose `.env`.
3. Run `docker compose up --build -d`, `docker compose exec web php artisan migrate --force`.
4. Open http://localhost:8080. Register a workspace owner, upload a supported audio file and start the AI job.

See `docs/architecture.md` and `docs/security.md` before any deployment. This compose stack is for local development; do not expose it directly to the public internet.

**Secrets, recordings and transcripts MUST NOT be committed.**

## Status

Bootstrap includes tenant-scoped recording flow, processing job orchestration and a minimal browser UI. Real-world speech accuracy, commercial billing, diarization, deployment hardening, object-storage rollout, legal compliance and production load testing are pending. See PR description for the exact release gaps.
