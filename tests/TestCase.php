<?php

namespace Derrytech\TelegramLogger\Tests;

use Derrytech\TelegramLogger\TelegramLoggingServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            TelegramLoggingServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('telegram-logger.token', '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11');
        $app['config']->set('telegram-logger.chat_id', '-100123456789');
        $app['config']->set('telegram-logger.level', 'debug');
        $app['config']->set('telegram-logger.enabled', true);
    }
}
