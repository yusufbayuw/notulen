<?php

return [
    'ai_service_url' => env('AI_SERVICE_URL', 'http://ai:8000'),
    'ai_internal_secret' => env('AI_INTERNAL_SECRET'),
    'ai_callback_url' => env('AI_CALLBACK_URL', 'http://web:8080/api/internal/ai/callback'),
    'ai_request_timeout' => (int) env('AI_REQUEST_TIMEOUT', 10),
    'max_upload_kilobytes' => 102400,
];
