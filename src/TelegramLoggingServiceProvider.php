<?php

namespace Derrytech\TelegramLogger;

use Derrytech\TelegramLogger\Logging\TelegramHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Monolog\Logger;

class TelegramLoggingServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/telegram-logger.php',
            'telegram-logger'
        );

        // Dynamically define telegram logging channel if not explicitly configured in host logging.channels
        if (empty($this->app['config']->get('logging.channels.telegram'))) {
            $this->app['config']->set('logging.channels.telegram', [
                'driver' => 'telegram',
                'token' => $this->app['config']->get('telegram-logger.token'),
                'chat_id' => $this->app['config']->get('telegram-logger.chat_id'),
                'topic_id' => $this->app['config']->get('telegram-logger.topic_id'),
                'level' => $this->app['config']->get('telegram-logger.level', 'debug'),
                'timeout' => $this->app['config']->get('telegram-logger.timeout', 2),
                'enabled' => $this->app['config']->get('telegram-logger.enabled', true),
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/telegram-logger.php' => config_path('telegram-logger.php'),
            ], 'telegram-logger-config');
        }

        Log::extend('telegram', function ($app, array $config) {
            $enabled = $config['enabled'] ?? config('telegram-logger.enabled', true);
            $token = $config['token'] ?? config('telegram-logger.token', '');
            $chatId = $config['chat_id'] ?? config('telegram-logger.chat_id', '');
            $topicId = $config['topic_id'] ?? config('telegram-logger.topic_id');
            $level = $config['level'] ?? config('telegram-logger.level', 'debug');
            $timeout = (int) ($config['timeout'] ?? config('telegram-logger.timeout', 2));

            return new Logger('telegram', [
                new TelegramHandler(
                    botToken: (string) $token,
                    chatId: (string) $chatId,
                    level: $level,
                    topicId: $topicId !== null ? (int) $topicId : null,
                    timeout: $timeout,
                    enabled: (bool) $enabled
                ),
            ]);
        });
    }
}
