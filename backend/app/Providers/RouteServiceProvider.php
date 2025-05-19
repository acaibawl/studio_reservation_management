<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // パスパラメータでdateが指定された変数はCarbonオブジェクトをバインドする
        Route::bind('date', function ($param) {
            $carbonDate = CarbonImmutable::createFromFormat('Y-m-d', $param);
            if ($carbonDate->format('Y-m-d') === $param) {
                return $carbonDate;
            }
            throw new NotFoundHttpException();
        });

        // パスパラメータでhourが指定された変数は0~23の数値しか受け付けない
        Route::bind('hour', function ($param) {
            if (is_numeric($param) && $param >= 0 && $param <= 23) {
                return (int) $param;
            }
            throw new NotFoundHttpException();
        });
    }
}
