<?php

use App\Http\Controllers\AiCallbackController;
use Illuminate\Support\Facades\Route;

Route::post('/internal/ai/callback', AiCallbackController::class)
    ->middleware('throttle:120,1');
