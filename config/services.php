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
        'key' => env('RAPIDAPI_KEY'),
        'allowed_hosts' => array_filter(array_map('trim', explode(',', env('RAPIDAPI_ALLOWED_HOSTS', 'linkedin-data-api.p.rapidapi.com,li-data-scraper.p.rapidapi.com,fresh-linkedin-profile-data.p.rapidapi.com'))))
    ],

    'calendly' => [
        'link' => env('CALENDLY_LINK', 'https://calendly.com/your-username'),
        'enabled' => env('CALENDLY_ENABLED', false),
        'client_id' => env('CALENDLY_CLIENT_ID'),
        'client_secret' => env('CALENDLY_CLIENT_SECRET'),
        'redirect' => env('CALENDLY_REDIRECT_URL'),
        'webhook_url' => env('CALENDLY_WEBHOOK_URL', 'https://app.linkdominator.com/api/calendly/webhook')
    ],

    'phantombuster' => [
        'api_key' => env('PHANTOMBUSTER_API_KEY'),
        'api_url' => env('PHANTOMBUSTER_API_URL', 'https://api.phantombuster.com/api/v1'),
        // LinkedIn Post Likers Export - extracts users who liked a specific post (requires post URL)
        'linkedin_post_likers_phantom_id' => env('PHANTOMBUSTER_LINKEDIN_POST_LIKERS_PHANTOM_ID'),
        // LinkedIn Post Commenters Export - extracts users who commented on a specific post (requires post URL)
        'linkedin_post_commenters_phantom_id' => env('PHANTOMBUSTER_LINKEDIN_POST_COMMENTERS_PHANTOM_ID'),
        // Limit how many company posts we process per run
        // Each post = 2 phantom calls (likers + commenters), so 5 posts = 10 calls
        // Lower = less PhantomBuster credits used, Higher = more data scraped
        'company_posts_limit' => env('COMPETITOR_POSTS_LIMIT', 15),
        
        // Stop early if we get enough engagers (saves credits)
        // Set to 0 to disable early stopping
        'min_engagers_for_early_stop' => env('PHANTOMBUSTER_MIN_ENGAGERS_EARLY_STOP', 1000),
        // Required for LinkedIn phantoms: Get manually from LinkedIn browser cookies
        // Steps: 1) Log into LinkedIn, 2) Open DevTools (F12) > Application > Cookies > linkedin.com, 3) Copy "li_at" cookie value
        'linkedin_session_cookie' => env('PHANTOMBUSTER_LINKEDIN_SESSION_COOKIE'),
        // Optional: Custom user agent (defaults to Chrome on Windows if not set)
        // To get: Open DevTools > Console > type: navigator.userAgent
        'linkedin_user_agent' => env('PHANTOMBUSTER_LINKEDIN_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
    ]
];
