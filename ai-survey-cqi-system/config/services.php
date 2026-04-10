<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

     // ── NLP Sentiment Server (Flask) ────────────────────────────────────────
    'nlp' => [
        'url'           => env('NLP_SERVER_URL',     'http://127.0.0.1:5000'),
        'timeout'       => env('NLP_SERVER_TIMEOUT', 30),
        'model_name'    => env('NLP_MODEL_NAME',     'distilbert'),
        'model_version' => env('NLP_MODEL_VERSION',  '5.0.0'),
    ],

    // ── Gemini AI ────────────────────────────────────────────────────────────
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', 'AIzaSyBI_guj9G9ByDo0lgsoi8BWvWjnITXKGTU'),
        'model'   => env('GEMINI_MODEL',   'gemini-2.5-flash'),
    ],

];
