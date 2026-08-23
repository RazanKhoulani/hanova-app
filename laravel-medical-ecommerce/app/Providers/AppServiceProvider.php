<?php

namespace App\Providers;

use App\Services\BundledProductImagePublisher;
use App\Services\Otp\HttpQVerifyClient;
use App\Services\Otp\QVerifyClient;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(QVerifyClient::class, function (): QVerifyClient {
            $baseUrl = trim((string) config('services.qverify.base_url'));
            $apiKey = trim((string) config('services.qverify.api_key'));

            if ($baseUrl === '' || $apiKey === '') {
                throw new RuntimeException('QVerify is not configured.');
            }

            return new HttpQVerifyClient($baseUrl, $apiKey);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Railway mounts the public storage volume only when the app runs, not
        // during pre-deploy. Make the bundled product images available there as
        // soon as the production application boots.
        if (! $this->app->runningInConsole()) {
            $this->app->make(BundledProductImagePublisher::class)->publishMissing();
        }
    }
}
