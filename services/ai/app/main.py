"""Private job intake API. Do not publish this listener to the internet."""
import os
import logging

import redis
from fastapi import FastAPI, HTTPException, Request
from pydantic import BaseModel, Field
from rq import Queue

from app.security import valid_signature

logger = logging.getLogger(__name__)
app = FastAPI(title="Notulen AI Internal", docs_url=None, redoc_url=None)
connection = redis.from_url(os.getenv("REDIS_URL", "redis://redis:6379/0"))
queue = Queue("notulen-audio", connection=connection)


class JobRequest(BaseModel):
    job_id: int = Field(gt=0)
    organization_id: int = Field(gt=0)
    meeting_id: int = Field(gt=0)
    storage_path: str = Field(min_length=1, max_length=512)
    language: str = Field(pattern="^(id|en)$")


@app.get("/health")
def health():
    try:
        connection.ping()
    except redis.RedisError:
        raise HTTPException(503, "Redis unavailable")
    return {"ok": True}


@app.post("/v1/jobs", status_code=202)
async def submit_job(request: Request):
    body = await request.body()
    if not valid_signature(
        body, request.headers.get("X-Timestamp"), request.headers.get("X-Signature")
    ):
        raise HTTPException(401, "Unauthorized")

    try:
        job = JobRequest.model_validate_json(body)
    except ValueError:
        raise HTTPException(422, "Invalid job payload")

    # Redis NX protects against concurrent duplicates and retried HTTP submissions.
    dedup_key = f"notulen:ai:accepted:{job.job_id}"
    accepted = connection.set(dedup_key, "1", nx=True, ex=604800)
    if accepted:
        try:
            queue.enqueue(
                "app.worker.process_recording",
                job.model_dump(),
                job_id=f"notulen-{job.job_id}",
                job_timeout=14400,
                result_ttl=86400,
                failure_ttl=604800,
            )
        except Exception:
            connection.delete(dedup_key)
            logger.exception("Failed to enqueue AI job %s", job.job_id)
            raise HTTPException(503, "Queue unavailable")

    return {"accepted": True, "job_id": job.job_id}
