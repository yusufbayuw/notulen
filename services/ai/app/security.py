"""HMAC request authentication for private service-to-service calls."""
import hashlib
import hmac
import os
import time


def valid_signature(body: bytes, timestamp: str | None, signature: str | None) -> bool:
    secret = os.getenv("AI_INTERNAL_SECRET", "")
    if len(secret) < 32 or not timestamp or not signature or not timestamp.isdigit():
        return False
    if abs(int(time.time()) - int(timestamp)) > 300:
        return False
    expected = hmac.new(
        secret.encode("utf-8"), timestamp.encode("ascii") + b"." + body, hashlib.sha256
    ).hexdigest()
    return hmac.compare_digest(expected, signature)
