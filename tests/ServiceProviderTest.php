<?php

namespace Derrytech\TelegramLogger\Tests;

use Illuminate\Support\Facades\Log;
use Monolog\Logger;

class ServiceProviderTest extends TestCase
{
    public function test_service_provider_injects_telegram_channel_config(): void
    {
        $channelConfig = config('logging.channels.telegram');

        $this->assertIsArray($channelConfig);
        $this->assertEquals('telegram', $channelConfig['driver']);
        $this->assertEquals('123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11', $channelConfig['token']);
        $this->assertEquals('-100123456789', $channelConfig['chat_id']);
    }

    public function test_log_facade_resolves_telegram_channel(): void
    {
        $logger = Log::channel('telegram')->getLogger();

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertEquals('telegram', $logger->getName());
        $this->assertCount(1, $logger->getHandlers());
    }
}
