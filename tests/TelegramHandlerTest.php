<?php

namespace Derrytech\TelegramLogger\Tests;

use Derrytech\TelegramLogger\Logging\TelegramHandler;
use Illuminate\Support\Facades\Http;
use Monolog\Level;
use Monolog\LogRecord;
use RuntimeException;

class TelegramHandlerTest extends TestCase
{
    public function test_format_message_escapes_html_and_renders_structure(): void
    {
        config(['app.name' => 'Testing <App> & Co']);

        $handler = new TelegramHandler('token', '12345');
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'telegram',
            level: Level::Error,
            message: 'User <test@example.com> failed login & retry',
            context: [],
            extra: []
        );

        $formatted = $handler->formatMessage($record);

        $this->assertStringContainsString('❌ <b>Error</b> — Testing &lt;App&gt; &amp; Co', $formatted);
        $this->assertStringContainsString('&lt;test@example.com&gt; failed login &amp; retry', $formatted);
        $this->assertStringNotContainsString('<App>', $formatted);
        $this->assertStringNotContainsString('<test@example.com>', $formatted);
    }

    public function test_format_message_renders_exception_details(): void
    {
        $handler = new TelegramHandler('token', '12345');
        $exception = new RuntimeException('Database connection <failed>');

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'telegram',
            level: Level::Critical,
            message: 'Uncaught Exception',
            context: ['exception' => $exception],
            extra: []
        );

        $formatted = $handler->formatMessage($record);

        $this->assertStringContainsString('🔥 <b>Critical</b>', $formatted);
        $this->assertStringContainsString('💥 Database connection &lt;failed&gt;', $formatted);
        $this->assertStringContainsString('📍', $formatted);
    }

    public function test_format_message_truncates_long_messages(): void
    {
        $handler = new TelegramHandler('token', '12345');
        $veryLongMessage = str_repeat('A', 5000);

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'telegram',
            level: Level::Info,
            message: $veryLongMessage,
            context: [],
            extra: []
        );

        $formatted = $handler->formatMessage($record);

        $this->assertLessThanOrEqual(4000, mb_strlen($formatted));
        $this->assertStringContainsString('[Truncated]', $formatted);
    }

    public function test_handler_posts_to_telegram_with_topic_id(): void
    {
        Http::fake();

        $handler = new TelegramHandler(
            botToken: 'bot123456:secret',
            chatId: '-100987654321',
            level: Level::Debug,
            topicId: 42,
            timeout: 3,
            enabled: true
        );

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'telegram',
            level: Level::Warning,
            message: 'Warning in thread',
            context: [],
            extra: []
        );

        // Execute write via handle
        $handler->handle($record);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'https://api.telegram.org/botbot123456:secret/sendMessage'
                && ($data['chat_id'] ?? null) === '-100987654321'
                && ($data['message_thread_id'] ?? null) === 42
                && ($data['parse_mode'] ?? null) === 'HTML';
        });
    }

    public function test_handler_skips_when_disabled_or_missing_credentials(): void
    {
        Http::fake();

        $handlerDisabled = new TelegramHandler('token', 'chat', Level::Debug, null, 2, false);
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'telegram',
            level: Level::Error,
            message: 'Test message',
            context: [],
            extra: []
        );

        $handlerDisabled->handle($record);

        Http::assertNothingSent();
    }
}
