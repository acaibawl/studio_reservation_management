<?php

declare(strict_types=1);

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\Owner\OwnerAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthCheckController::class, 'index'])->name('health');

Route::prefix('owner-auth')
    ->name('owner-auth.')
    ->group(function () {
        Route::post('/login', [OwnerAuthController::class, 'login'])->name('login');
        Route::middleware('auth:api_owner')->group(function () {
            Route::get('/me', [OwnerAuthController::class, 'me'])->name('me');
            Route::post('/logout', [OwnerAuthController::class, 'logout'])->name('logout');
            Route::post('/refresh', [OwnerAuthController::class, 'refresh'])->name('refresh');
        });
    });
