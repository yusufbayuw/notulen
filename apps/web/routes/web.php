<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RecordingController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'registerPage'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', function () {
        $organization = request()->user()->organizations()->first();

        return $organization
            ? redirect()->route('workspace.dashboard', $organization)
            : redirect()->route('register');
    })->name('home');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('w/{organization:slug}')
        ->name('workspace.')
        ->middleware('workspace.member')
        ->group(function (): void {
            Route::get('/', [WorkspaceController::class, 'dashboard'])->name('dashboard');
            Route::post('/recordings', [RecordingController::class, 'store'])
                ->middleware('throttle:10,1')->name('recordings.store');
            Route::get('/meetings/{meeting}', [WorkspaceController::class, 'showMeeting'])
                ->whereNumber('meeting')->name('meetings.show');
        });
});
