<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\DateFactory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // eloquentの日付型をすべてCarbonImmutableで扱う
        DateFactory::use(CarbonImmutable::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 本番環境以外ではN+1検知で例外にする
        Model::shouldBeStrict(! $this->app->isProduction());
        // resourceクラスの返却値をdataキーで囲まない
        JsonResource::withoutWrapping();
    }
}
