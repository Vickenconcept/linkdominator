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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'chatgpt' => [
        'key' => env('OPENAI_KEY')
    ],

    'linkedin' => [
        'api' => env('LINKEDIN_API'),
        'client' => env('LINKEDIN_CLIENT'),
        'secret' => env('LINKEDIN_SECRET'),
        'state' => env('LINKEDIN_STATE')
    ],

    'skrapp' => [
        'key' => env('SKRAPP_EMAIL_FINDER_KEY')
    ],

    'rapidapi' => [
        'key' => env('RAPIDAPI_KEY')
    ],

    'calendly' => [
        'link' => env('CALENDLY_LINK', 'https://calendly.com/your-username'),
        'enabled' => env('CALENDLY_ENABLED', false)
    ]
];
