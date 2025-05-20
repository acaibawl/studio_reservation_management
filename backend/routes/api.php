<?php

declare(strict_types=1);

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\Owner\BusinessDayController;
use App\Http\Controllers\Owner\MemberController;
use App\Http\Controllers\Owner\OwnerAuthController;
use App\Http\Controllers\Owner\ReservationController;
use App\Http\Controllers\Owner\StudioController;
use App\Http\Controllers\Owner\TemporaryClosingDayController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthCheckController::class, 'index'])->name('health');

Route::prefix('owner-auth')
    ->name('owner-auth.')
    ->group(function () {
        // ログインは認証ミドルウェアを適用させない
        Route::post('/login', [OwnerAuthController::class, 'login'])->name('login');
        Route::middleware('auth:api_owner')->group(function () {
            Route::get('/me', [OwnerAuthController::class, 'me'])->name('me');
            Route::post('/logout', [OwnerAuthController::class, 'logout'])->name('logout');
            Route::post('/refresh', [OwnerAuthController::class, 'refresh'])->name('refresh');
        });
    });

// オーナーのログイン時のみアクセス可能
Route::middleware('auth:api_owner')->group(function () {
    Route::prefix('owner')
        ->name('owner.')
        ->group(function () {
            Route::get('business-day', [BusinessDayController::class, 'index'])->name('business-day.index');
            Route::put('business-day', [BusinessDayController::class, 'update'])->name('business-day.update');

            Route::prefix('temporary-closing-days')
                ->name('temporary-closing-days.')
                ->group(function () {
                    Route::get('/', [TemporaryClosingDayController::class, 'index'])->name('index');
                    Route::post('/', [TemporaryClosingDayController::class, 'store'])->name('store');
                    Route::delete('/{temporaryClosingDay:date}', [TemporaryClosingDayController::class, 'destroy'])->name('destroy');
                });

            Route::prefix('studios')
                ->name('studios.')
                ->group(function () {
                    Route::get('/', [StudioController::class, 'index'])->name('index');
                    Route::post('/', [StudioController::class, 'store'])->name('store');
                    Route::get('/{studio}', [StudioController::class, 'show'])->name('show');
                    Route::put('/{studio}', [StudioController::class, 'update'])->name('update');
                    Route::delete('/{studio}', [StudioController::class, 'destroy'])->name('destroy');
                });

            Route::prefix('members')
                ->name('members.')
                ->group(function () {
                    Route::get('/', [MemberController::class, 'index'])->name('index');
                    Route::get('/{member}', [MemberController::class, 'show'])->name('show');
                });

            Route::prefix('reservations')
                ->name('reservations.')
                ->group(function () {
                    Route::post('/', [ReservationController::class, 'store'])->name('store');
                    Route::get('/{reservation}', [ReservationController::class, 'show'])->name('show');
                    Route::patch('/{reservation}', [ReservationController::class, 'update'])->name('update');
                    Route::delete('/{reservation}', [ReservationController::class, 'destroy'])->name('destroy');
                    Route::get('/get-quotas-by-date/{date}', [ReservationController::class, 'getQuotasByDate'])->name('get-quotas-by-date');
                    Route::get('/studios/{studio}/{date}/{hour}/max-available-hour', [ReservationController::class, 'getMaxAvailableHour'])->name('max-available-hour');
                });
        });
});
