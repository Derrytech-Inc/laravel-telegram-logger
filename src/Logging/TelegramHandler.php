<?php

namespace LaundriGo\TelegramLogger\Logging;

use Illuminate\Support\Str;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Illuminate\Support\Facades\Http;

class TelegramHandler extends AbstractProcessingHandler
{
    protected string $botToken;
    protected string $chatId;

    public function __construct(string $botToken, string $chatId, $level = Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->botToken = $botToken;
        $this->chatId = $chatId;
    }

    /**
     * Writes the record down to the log of the implementing handler
     */
    protected function write(LogRecord $record): void
    {
        // Silently skip if credentials are missing
        if (empty($this->botToken) || empty($this->chatId)) {
            return;
        }

        $message = $this->formatMessage($record);

        try {
            Http::timeout(5)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);
        } catch (\Exception $e) {
            // Silently fail to avoid recursive logging issues
        }
    }

    /**
     * Format the Monolog LogRecord to HTML for Telegram
     */
    protected function formatMessage(LogRecord $record): string
    {
        $emoji = match (Str::headline($record->level->name)) {
            'Debug' => '🔍',
            'Info' => 'ℹ️',
            'Notice' => '📌',
            'Warning' => '⚠️',
            'Error' => '❌',
            'Critical' => '🔥',
            'Alert' => '🚨',
            'Emergency' => '💀',
            default => '📋',
        };

        $safeMessage = htmlspecialchars(
            substr($record->message, 0, 3000),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        $message = implode("\n", [
            "{$emoji} <b>{$record->level->name}</b> — ".config('app.name'),
            "<pre>{$safeMessage}</pre>",
            '<i>'.now()->toDateTimeString().'</i>',
        ]);

        if (! empty($record->context)) {
            $exception = $record->context['exception'] ?? null;
            $traceText = '';

            if ($exception instanceof \Throwable) {
                $traceText = implode("\n", [
                    '📍 ' . $exception->getFile() . ':' . $exception->getLine(),
                    '',
                    // First 5 frames is usually enough to find your code
                    collect($exception->getTrace())
                        ->take(5)
                        ->map(fn($frame) => ($frame['file'] ?? '[internal]') . ':' . ($frame['line'] ?? '?'))
                        ->implode("\n"),
                ]);
            } else {
                $traceText = substr(json_encode($record->context, JSON_PRETTY_PRINT), 0, 1000);
            }

            $safeTrace = htmlspecialchars($traceText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $message .= "\n\n<b>Trace:</b>\n<pre>{$safeTrace}</pre>";
        }

        return $message;
    }
}
