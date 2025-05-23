<?php

declare(strict_types=1);

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\Member\Auth\ChangeEmailVerifiedCodeController;
use App\Http\Controllers\Member\Auth\EmailUpdateController;
use App\Http\Controllers\Member\Auth\MemberController as AuthMemberController;
use App\Http\Controllers\Member\Auth\PasswordResetController;
use App\Http\Controllers\Member\Auth\SignUpEmailVerifiedCodeController;
use App\Http\Controllers\Member\ReservationController as MemberReservationController;
use App\Http\Controllers\Owner\BusinessDayController;
use App\Http\Controllers\Owner\MemberController;
use App\Http\Controllers\Owner\OwnerAuthController;
use App\Http\Controllers\Owner\ReservationController;
use App\Http\Controllers\Owner\StudioController;
use App\Http\Controllers\Owner\TemporaryClosingDayController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthCheckController::class, 'index'])->name('health');

Route::prefix('member-auth')
    ->name('member-auth.')
    ->group(function () {
        Route::prefix('sign-up-email-verified-code')
            ->name('sign-up-email-verified-code.')
            ->group(function () {
                Route::post('/send', [SignUpEmailVerifiedCodeController::class, 'send'])->name('send');
                Route::post('/verify', [SignUpEmailVerifiedCodeController::class, 'verify'])->name('verify');
            });

        Route::prefix('password-reset')
            ->name('password-reset.')
            ->group(function () {
                Route::post('/send-email', [PasswordResetController::class, 'sendEmail'])->name('send-email');
                Route::post('/reset', [PasswordResetController::class, 'reset'])->name('reset');
            });

        Route::post('/login', [AuthMemberController::class, 'login'])->name('login');

        Route::prefix('member')
            ->name('member.')
            ->group(function () {
                Route::post('/', [AuthMemberController::class, 'store'])->name('store');
                Route::put('/', [AuthMemberController::class, 'update'])->middleware('auth:api_member')->name('update');
            });

        Route::middleware('auth:api_member')->group(function () {
            Route::get('/me', [AuthMemberController::class, 'showMe'])->name('me');
            Route::post('/logout', [AuthMemberController::class, 'logout'])->name('logout');
            Route::post('/change-email-verified-code/send', [ChangeEmailVerifiedCodeController::class, 'send'])->name('change-email-verified-code.send');
            Route::patch('/email', [EmailUpdateController::class, 'update'])->name('email.update');
        });
    });

Route::middleware('auth:api_member')->group(function () {
    Route::get('/reservation_availability/date/{date}', [MemberReservationController::class, 'getAvailabilityByDate'])->name('reservation-availability.date');
    Route::prefix('reservations')->name('reservations.')->group(function () {
        Route::get('/studios/{studio}/{date}/{hour}/max-available-hour', [MemberReservationController::class, 'getMaxAvailableHour'])->name('get-max-available-hour');
//        Route::post('/', [MemberReservationController::class, 'store'])->name('store');
    });
    Route::post('/studios/{studio}/reservations', [MemberReservationController::class, 'store'])->name('store');
});

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
                    Route::get('/get-quotas-by-date/{date}', [ReservationController::class, 'getQuotasByDate'])->name('get-quotas-by-date');
                    Route::get('/studios/{studio}/{date}/{hour}/max-available-hour', [ReservationController::class, 'getMaxAvailableHour'])->name('max-available-hour');
                });

            Route::prefix('studios/{studio}/reservations')
                ->name('studios.reservations.')
                ->group(function () {
                    Route::post('/', [ReservationController::class, 'store'])->name('store');
                    Route::get('/{reservation}', [ReservationController::class, 'show'])->name('show');
                    Route::patch('/{reservation}', [ReservationController::class, 'update'])->name('update');
                    Route::delete('/{reservation}', [ReservationController::class, 'destroy'])->name('destroy');
                });
        });
});
