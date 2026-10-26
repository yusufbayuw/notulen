# Notulen AI

Indonesian-first, self-hosted AI meeting transcription and notes SaaS.

**Current status: early development scaffold. NOT production-ready, not yet suitable for paying customers.**

## Stack
Laravel 13 + Vue 3/Inertia (web), MariaDB + Redis (data/queues), Python FastAPI + RQ + faster-whisper (speech), optional local Ollama (AI summary).

## Quick start — local Docker development

1. Copy root `.env.example` → `.env`. **Change** all sample passwords and set a 32+-character random `AI_INTERNAL_SECRET`.
2. Copy `apps/web/.env.example` → `apps/web/.env`. Set `APP_KEY` to a fresh key: run `docker compose run --rm web php artisan key:generate --show` (then paste its output into the file). Set `APP_DEBUG=true` only locally.
3. Run `docker compose up --build -d`.
4. Run `docker compose exec web php artisan migrate --force`.
5. Visit http://localhost:8080, register, upload a recording and refresh the meeting page until processing completes.

**Local LLM summary (optional)**: run `docker compose --profile llm up -d ollama`, then `docker compose exec ollama ollama pull qwen3:4b`. Set root `OLLAMA_MODEL=qwen3:4b` and recreate `ai-worker`. Without it, transcription may work but summary remains unavailable.

Only loopback port 8080 is exposed in the local stack. The local AI worker needs an accessible media volume and may download model weights on its first run.

## Architecture and security
See [architecture](docs/architecture.md), [security checklist](docs/security.md), and [implementation backlog](docs/backlog.md).

The following are **not yet implemented**: billing, verified email and password reset, speaker diarization, DOCX/PDF exports, production quota enforcement, evidence-linked decisions, SaaS compliance and release security review. No customer files or keys belong in Git.

## Development validation
- Web: `cd apps/web && composer install && php artisan test && npm install && npm run build`
- AI: `cd services/ai && pip install -r requirements.txt pytest && pytest -q`
- Workflow: `.github/workflows/ci.yml`.

Run a complete multi-container smoke test and restore drill before any production deployment.
