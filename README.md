# Laravel Telegram Logger

A lightweight, self-configuring Laravel package that seamlessly routes log records and exception notifications to a Telegram chat, channel, or supergroup topic.

[![PHP Version](https://img.shields.io/badge/php-%5E8.2-8892BF.svg)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-10.x%20%7C%2011.x%20%7C%2012.x%20%7C%2013.x-FF2D20.svg)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

---

## ✨ Features

- ⚡ **Zero-Config Setup**: Automatically registers the `telegram` log channel into Laravel's logging configuration out of the box.
- 🎨 **Rich HTML Formatting**: Renders formatted log messages complete with level emojis, timestamp, application name, and HTML code blocks.
- 📍 **Smart Stack Traces**: Formats exceptions with file location, message, and concise trace frames.
- 🛡️ **Telegram Limits & Entity Safety**: Automatically escapes HTML special characters (`<`, `>`, `&`) and enforces total payload truncation under 4,000 characters to comply with Telegram's API limits.
- 💬 **Forum Topics Support**: Direct logs to specific Supergroup Forum Topics using `TELEGRAM_TOPIC_ID`.
- 🔒 **Config Cache Compliant**: Never calls `env()` at runtime, remaining fully compatible with `php artisan config:cache`.
- ⏱️ **Non-Blocking Reliability**: Low, configurable HTTP timeout (default 2s) and silent failure handling to protect web requests from network delays.

---

## 📦 Requirements

- **PHP**: `^8.2`
- **Laravel**: `^10.0 | ^11.0 | ^12.0 | ^13.0`

---

## 🚀 Installation

Install the package via Composer:

```bash
composer require derrytech/telegram-logger
```

The package uses Laravel's Package Auto-Discovery and will automatically register the `TelegramLoggingServiceProvider`.

---

## 🔑 Environment Setup

Add your Telegram bot credentials to your application's `.env` file:

```env
# Telegram Logger
TELEGRAM_BOT_TOKEN="123456789:ABCdefGHIjklMNOpqrsTUVwxyZ"
TELEGRAM_CHAT_ID="-100123456789"

# Optional Settings
TELEGRAM_TOPIC_ID= # Optional: Forum Topic Thread ID
TELEGRAM_LOG_LEVEL=debug # Minimum log level (default: debug)
TELEGRAM_LOG_TIMEOUT=2 # Request timeout in seconds (default: 2)
TELEGRAM_LOG_ENABLED=true # Toggle logging on/off
```

---

## ⚙️ Configuration (Optional)

If you wish to customize the default package configuration, publish the config file using `artisan`:

```bash
php artisan vendor:publish --tag=telegram-logger-config
```

This creates `config/telegram-logger.php`:

```php
return [
    'enabled'  => env('TELEGRAM_LOG_ENABLED', true),
    'token'    => env('TELEGRAM_BOT_TOKEN'),
    'chat_id'  => env('TELEGRAM_CHAT_ID'),
    'topic_id' => env('TELEGRAM_TOPIC_ID'),
    'level'    => env('TELEGRAM_LOG_LEVEL', 'debug'),
    'timeout'  => (int) env('TELEGRAM_LOG_TIMEOUT', 2),
];
```

---

## 💻 Usage

### 1. Add Telegram to your Log Stack

To receive Telegram alerts alongside standard daily/single logs, edit `config/logging.php` and append `telegram` to your `stack` channel:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'telegram'],
        'ignore_exceptions' => false,
    ],
    // ...
],
```

### 2. Log Directly to Telegram

You can also send messages directly to the `telegram` channel:

```php
use Illuminate\Support\Facades\Log;

// Standard logging
Log::channel('telegram')->error('Payment gateway timeout', [
    'user_id' => 42,
    'amount' => 199.99,
]);

// Helper level log
Log::channel('telegram')->critical('Database connection failure');
```

---

## 🤖 Obtaining Telegram Credentials

1. **Create a Bot**: Open Telegram, search for [@BotFather](https://t.me/BotFather), send `/newbot`, and copy the access token.
2. **Find Your Chat ID**:
   - For Personal Chat: Message [@userinfobot](https://t.me/userinfobot) to get your Chat ID.
   - For Groups/Channels: Add your bot to the group, send a message, and fetch updates via:
     `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates`
   - Grab the `"id"` under the `"chat"` object (group IDs typically start with `-100`).

---

## 🧪 Testing

Run the test suite via PHPUnit:

```bash
vendor/bin/phpunit
```

---

## 📄 License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
