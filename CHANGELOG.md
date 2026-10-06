# Changelog

All notable changes to `derrytech/telegram-logger` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-10-06

### Added
- Initial stable release.
- Zero-config auto-registration for Laravel logging channels (`telegram`).
- Formatted Telegram HTML log notifications with level emojis, app name, and timestamp.
- Exception formatting with file, line numbers, and truncated backtraces.
- Telegram message truncation protection (staying within Telegram 4,096 char limit) with entity safety.
- Telegram Supergroup Forum Topic support via `TELEGRAM_TOPIC_ID`.
- Config cache compatibility (`artisan config:cache`).
- Configurable HTTP timeout and silent exception handling to protect request lifecycle.
- Automated GitHub Actions test matrix across PHP 8.2-8.4 and Laravel 10-13.
- Automated GitHub release pipeline on version tags.
