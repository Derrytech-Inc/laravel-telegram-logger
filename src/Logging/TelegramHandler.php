<?php

namespace Derrytech\TelegramLogger\Logging;

use Illuminate\Support\Facades\Http;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Throwable;

class TelegramHandler extends AbstractProcessingHandler
{
    protected string $botToken;
    protected string $chatId;
    protected ?int $topicId;
    protected int $timeout;
    protected bool $enabled;

    /**
     * @param string $botToken Telegram Bot API token
     * @param string $chatId Telegram Chat ID or channel username
     * @param int|string|Level $level Minimum log level
     * @param int|null $topicId Optional Telegram Supergroup Forum Topic Thread ID
     * @param int $timeout Request timeout in seconds
     * @param bool $enabled Enable or disable handler execution
     * @param bool $bubble Whether messages should bubble up the Monolog stack
     */
    public function __construct(
        string $botToken,
        string $chatId,
        $level = Level::Debug,
        ?int $topicId = null,
        int $timeout = 2,
        bool $enabled = true,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
        $this->botToken = trim($botToken);
        $this->chatId = trim($chatId);
        $this->topicId = $topicId;
        $this->timeout = max(1, $timeout);
        $this->enabled = $enabled;
    }

    /**
     * Writes the record down to Telegram via Bot API.
     */
    protected function write(LogRecord $record): void
    {
        if (!$this->enabled || empty($this->botToken) || empty($this->chatId)) {
            return;
        }

        $message = $this->formatMessage($record);

        $payload = [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if ($this->topicId !== null) {
            $payload['message_thread_id'] = $this->topicId;
        }

        try {
            Http::timeout($this->timeout)->post(
                "https://api.telegram.org/bot{$this->botToken}/sendMessage",
                $payload
            );
        } catch (Throwable $e) {
            if (defined('PHPUNIT_COMPOSER_INSTALL') || class_exists(\PHPUnit\Framework\TestCase::class, false)) {
                fwrite(STDERR, "\n[DEBUG WRITE EXCEPTION]: " . get_class($e) . ': ' . $e->getMessage() . " at " . $e->getFile() . ':' . $e->getLine() . "\n");
            }
            // Silently catch exceptions to prevent recursive logging loops
        }
    }

    /**
     * Format the Monolog LogRecord to Telegram HTML parse mode.
     */
    public function formatMessage(LogRecord $record): string
    {
        $levelName = strtoupper($record->level->name);

        $emoji = match ($levelName) {
            'DEBUG' => '🔍',
            'INFO' => 'ℹ️',
            'NOTICE' => '📌',
            'WARNING' => '⚠️',
            'ERROR' => '❌',
            'CRITICAL' => '🔥',
            'ALERT' => '🚨',
            'EMERGENCY' => '💀',
            default => '📋',
        };

        $appName = $this->escapeHtml((string) config('app.name', 'Laravel'));
        $safeLevel = $this->escapeHtml($record->level->name);
        $header = "{$emoji} <b>{$safeLevel}</b> — {$appName}";
        $timeStr = '<i>' . $this->escapeHtml(now()->toDateTimeString()) . '</i>';

        $rawMessage = (string) $record->message;
        $isMessageTruncated = mb_strlen($rawMessage) > 2000;
        $safeMessage = $this->escapeHtml(mb_substr($rawMessage, 0, 2000));
        if ($isMessageTruncated) {
            $safeMessage .= "\n... [Truncated]";
        }

        $output = "{$header}\n<pre>{$safeMessage}</pre>\n{$timeStr}";

        if (!empty($record->context)) {
            $contextText = $this->formatContext($record->context);
            if (!empty($contextText)) {
                $isContextTruncated = mb_strlen($contextText) > 1200;
                $safeContext = $this->escapeHtml(mb_substr($contextText, 0, 1200));
                if ($isContextTruncated) {
                    $safeContext .= "\n... [Truncated]";
                }
                $output .= "\n\n<b>Context / Trace:</b>\n<pre>{$safeContext}</pre>";
            }
        }

        // Guarantee final message fits well within Telegram's 4096 character limit
        if (mb_strlen($output) > 3900) {
            return mb_substr($output, 0, 3850) . "\n... [Truncated]</pre>";
        }

        return $output;
    }

    /**
     * Format exception or array context into readable string.
     */
    protected function formatContext(array $context): string
    {
        $exception = $context['exception'] ?? null;

        if ($exception instanceof Throwable) {
            $traceFrames = collect($exception->getTrace())
                ->take(5)
                ->map(function ($frame) {
                    $class = $frame['class'] ?? '';
                    $type = $frame['type'] ?? '';
                    $func = $frame['function'] ?? '';
                    $file = isset($frame['file']) ? basename($frame['file']) : '[internal]';
                    $line = $frame['line'] ?? '?';
                    $call = $class ? "{$class}{$type}{$func}()" : "{$func}()";

                    return "  at {$call} ({$file}:{$line})";
                })
                ->implode("\n");

            return implode("\n", array_filter([
                '📍 ' . $exception->getFile() . ':' . $exception->getLine(),
                '💥 ' . $exception->getMessage(),
                '',
                $traceFrames,
            ]));
        }

        return (string) json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Safely escape HTML characters for Telegram parse mode.
     */
    protected function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
