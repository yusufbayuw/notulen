import hashlib
import hmac
import os
import time

from app.security import valid_signature


def test_rejects_absent_secret(monkeypatch):
    monkeypatch.delenv("AI_INTERNAL_SECRET", raising=False)
    assert not valid_signature(b"{}", str(int(time.time())), "fake")


def test_signed_body_and_timestamp(monkeypatch):
    secret = "a" * 40
    monkeypatch.setenv("AI_INTERNAL_SECRET", secret)
    ts = str(int(time.time()))
    body = b'{"job_id":1}'
    digest = hmac.new(secret.encode(), ts.encode() + b"." + body, hashlib.sha256).hexdigest()
    assert valid_signature(body, ts, digest)
    assert not valid_signature(b'{"job_id":2}', ts, digest)
    assert not valid_signature(body, str(int(time.time()) - 900), digest)
