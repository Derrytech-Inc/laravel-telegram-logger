<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable / Disable Telegram Logging
    |--------------------------------------------------------------------------
    |
    | Set to false to disable sending log notifications to Telegram without
    | removing the logger channel configuration.
    |
    */

    'enabled' => env('TELEGRAM_LOG_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Credentials
    |--------------------------------------------------------------------------
    |
    | Your Telegram Bot API token obtained from @BotFather and the destination
    | Chat ID or Channel username (e.g. "@mychannel" or "-1001234567890").
    |
    */

    'token' => env('TELEGRAM_BOT_TOKEN'),

    'chat_id' => env('TELEGRAM_CHAT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Telegram Forum Topic ID (Optional)
    |--------------------------------------------------------------------------
    |
    | If your supergroup has Forum Topics enabled, specify the thread/topic ID
    | to route log messages into a specific topic channel.
    |
    */

    'topic_id' => env('TELEGRAM_TOPIC_ID'),

    /*
    |--------------------------------------------------------------------------
    | Log Level
    |--------------------------------------------------------------------------
    |
    | The minimum log level that triggers Telegram notifications. Valid values:
    | debug, info, notice, warning, error, critical, alert, emergency.
    |
    */

    'level' => env('TELEGRAM_LOG_LEVEL', 'debug'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time in seconds to wait for Telegram API HTTP requests before
    | timing out. Kept low to avoid blocking PHP process execution.
    |
    */

    'timeout' => (int) env('TELEGRAM_LOG_TIMEOUT', 2),

];
