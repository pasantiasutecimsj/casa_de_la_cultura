<?php

return [

    'registro_personas' => [
        'base_url' => env('REGISTRO_PERSONAS_URL', env('URL_REGISTRO_PERSONAS')),
        'timeout' => (int) env('REGISTRO_PERSONAS_TIMEOUT', 10),
    ],

    'registro_usuarios' => [
        'proyecto_clave' => env(
            'REGISTRO_USUARIOS_PROYECTO_CLAVE',
            env('USUARIOS_API_PROYECTO_CLAVE')
        ),
    ],

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

];
