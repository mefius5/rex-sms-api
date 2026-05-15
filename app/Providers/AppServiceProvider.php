<?php

namespace App\Providers;

use App\Contracts\SmsProvider;
use App\Services\Sms\SmsApiPlProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SmsProvider::class, function () {
            $driver = config('sms.default');
            $config = config("sms.providers.{$driver}");

            return match ($driver) {
                'smsapi' => new SmsApiPlProvider(
                    apiToken: $config['api_token'],
                    baseUrl: $config['base_url'],
                    from: $config['from'] ?? null,
                ),
                default => throw new \InvalidArgumentException("Unknown SMS provider: {$driver}"),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
