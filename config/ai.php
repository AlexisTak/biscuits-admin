<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Provider
    |--------------------------------------------------------------------------
    |
    | Choix du provider: 'openai' ou 'anthropic'
    |
    */
    'provider' => env('AI_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    */
    'openai_api_key' => env('OPENAI_API_KEY'),
    'openai_model' => env('OPENAI_MODEL', 'gpt-4-turbo-preview'),

    /*
    |--------------------------------------------------------------------------
    | Anthropic Configuration
    |--------------------------------------------------------------------------
    */
    'anthropic_api_key' => env('ANTHROPIC_API_KEY'),
    'anthropic_model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'requests_per_minute' => env('AI_RATE_LIMIT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Limits
    |--------------------------------------------------------------------------
    */
    'max_message_length' => env('AI_MAX_MESSAGE_LENGTH', 4000),
    'max_messages_per_conversation' => env('AI_MAX_MESSAGES', 100),
    'max_history_messages' => env('AI_MAX_HISTORY', 20),

    /*
    |--------------------------------------------------------------------------
    | Model Parameters
    |--------------------------------------------------------------------------
    */
    'max_tokens' => env('AI_MAX_TOKENS', 1000),
    'temperature' => env('AI_TEMPERATURE', 0.7),
];