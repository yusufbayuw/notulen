# Security review and production blockers

The repository is public. NEVER commit `.env`, credentials, audio files, transcripts, customer identifiers or actual production data.

## Implemented safeguards

- Web session-based auth and CSRF protections; throttled login/registration/upload.
- Workspace membership middleware and explicit workspace scope on all meeting detail/audio queries.
- Recordings reside on a non-public Laravel disk; media route requires workspace access.
- FastAPI request and callback HMAC-SHA256 over timestamp + exact body (5-minute window).
- Job intake deduplication and locked callback idempotency.
- Worker validates organization/meeting-specific paths and prevents traversal through resolved paths.
- CPU/ffmpeg workload has duration and process time limits.

## Blockers before commercial production

1. **Billing/quotas**: implement atomic usage reservation, consumption ledger and payment webhook checks.
2. **Identity**: verified email, password recovery, MFA for privileged users, brute-force/abuse monitoring.
3. **Tenancy**: external authorization review, object-storage access tests, background job tenant context tests and broader negative test matrix.
4. **Media**: malware scanning/quarantine, safe metadata extraction, content security policy, download/range support, retention and deletion.
5. **AI**: prompt-injection defense and evidence-linked action items, diarization and accuracy evaluation on consented Bahasa Indonesia recordings.
6. **Operational**: real reverse proxy TLS, encryption/keys, network policies, least-privileged IAM, logs redaction, monitoring, backups, isolated restore and incident response.
7. **Supply chain**: lock PHP/npm/Python dependency versions, dependency and image scans, SBOM, license compliance.
8. **Compliance**: Indonesian PDP law, lawful recording basis, privacy/terms, deletion rights, PSE registration applicability, DPA.

## Known limitations of the bootstrap

- HMAC alone prevents unauthorized submission but a valid signed callback can be replayed inside its time window; final state is idempotent, and stronger nonce tracking can be added.
- During upload, no quota/concurrency reservation is enforced.
- Local disk shared as read-only to the AI worker is intended for a local development environment; production must use tenant-scoped short-lived object storage credentials or a restricted transfer service.
- The development Compose stack uses Laravel's built-in server, not an audited production deployment.
