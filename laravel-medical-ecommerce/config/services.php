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

    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'credentials_path' => env('FIREBASE_CREDENTIALS_PATH'),
        'credentials_base64' => env('FIREBASE_CREDENTIALS_BASE64'),
        'web_api_key' => env('FIREBASE_WEB_API_KEY', 'AIzaSyCx5k2Ex9fvtW4S6AAFSAvk-gw-D5xO3lk'),
        'web_app_id' => env('FIREBASE_WEB_APP_ID', '1:1009458279788:web:b106e0341471b9dc68af49'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID', '1009458279788'),
        'web_vapid_key' => env('FIREBASE_WEB_VAPID_KEY'),
    ],

    'qverify' => [
        'base_url' => env('QVERIFY_BASE_URL', 'https://verify-api.qomratech.com/api'),
        'api_key' => env('QVERIFY_API_KEY'),
        'template_key' => env('QVERIFY_TEMPLATE_KEY', 'verify_otp_app'),
        'locale' => env('QVERIFY_LOCALE', 'en'),
        'app_name' => env('QVERIFY_APP_NAME', env('APP_NAME', 'Hanova')),
        'verify_ssl' => env('QVERIFY_VERIFY_SSL', true),
    ],

    'support_phone' => env('SUPPORT_PHONE', '+963 951 582 835'),

];
