FROM python:3.12-slim
ENV PYTHONDONTWRITEBYTECODE=1 PYTHONUNBUFFERED=1
RUN apt-get update && apt-get install -y --no-install-recommends ffmpeg && rm -rf /var/lib/apt/lists/*
WORKDIR /srv
COPY services/ai/requirements.txt /srv/requirements.txt
RUN pip install --no-cache-dir -r /srv/requirements.txt
COPY services/ai/app /srv/app
RUN useradd --create-home --uid 10001 aiuser
USER aiuser
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8000"]
