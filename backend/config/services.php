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

    // Redes de la cátedra en la landing. Si falta la URL, esa red no se muestra.
    'redes' => [
        'instagram' => [
            'url' => env('INSTAGRAM_URL'),
            // Token de larga duración inicial; luego lo renueva `php artisan redes:instagram-token`.
            'token' => env('INSTAGRAM_ACCESS_TOKEN'),
        ],
        'facebook' => [
            'url' => env('FACEBOOK_URL'),
            // El widget de "últimas publicaciones" solo funciona con Páginas, no con perfiles personales.
            'embed' => (bool) env('FACEBOOK_EMBED', false),
        ],
        'linkedin' => [
            'url' => env('LINKEDIN_URL'),
            // LinkedIn no permite incrustar "lo último": URL de inserción de un post concreto
            // (Post → ··· → Insertar esta publicación), p. ej. https://www.linkedin.com/embed/feed/update/urn:li:share:123
            'post_embed' => env('LINKEDIN_POST_EMBED_URL'),
        ],
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
