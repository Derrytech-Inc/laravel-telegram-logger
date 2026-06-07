<?php

namespace LaundriGo\TelegramLogger;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use LaundriGo\TelegramLogger\Logging\TelegramHandler;
use Monolog\Logger;

class TelegramLoggingServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Dynamically set telegram config if not already defined in logging.channels
        if (empty($this->app['config']->get('logging.channels.telegram'))) {
            $this->app['config']->set('logging.channels.telegram', [
                'driver' => 'telegram',
                'token' => env('TELEGRAM_BOT_TOKEN'),
                'chat_id' => env('TELEGRAM_CHAT_ID'),
                'level' => env('TELEGRAM_LOG_LEVEL', env('LOG_LEVEL', 'debug')),
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Log::extend('telegram', function ($app, array $config) {
            return new Logger('telegram', [
                new TelegramHandler(
                    $config['token'] ?? '',
                    $config['chat_id'] ?? '',
                    $config['level'] ?? 'debug'
                ),
            ]);
        });
    }
}
