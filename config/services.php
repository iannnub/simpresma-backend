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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'telegram' => [
        'bot_token'    => env('TELEGRAM_BOT_TOKEN'),
        'bot_username' => env('TELEGRAM_BOT_USERNAME', 'simpresma_unej_bot'),
    ],

    'gapura' => [
        'url'                 => env('GAPURA_URL', 'https://gapura.unej.ac.id'),
        'client_id'           => env('GAPURA_CLIENT_ID'),
        'client_secret'       => env('GAPURA_CLIENT_SECRET'),
        'redirect_uri'        => env('GAPURA_REDIRECT_URI', 'http://localhost:8000/api/auth/gapura/callback'),
        'frontend_url'        => env('GAPURA_FRONTEND_CALLBACK_URL', 'http://localhost:5173/auth/callback'),
        'logout_redirect_uri' => env('GAPURA_LOGOUT_REDIRECT_URI', 'http://localhost:5173/'),
    ],

];
