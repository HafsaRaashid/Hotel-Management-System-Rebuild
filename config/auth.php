<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication boundary (SQ-004) - mechanism only
    |--------------------------------------------------------------------------
    |
    | SQ-004 decided real authentication/authorization is required, sized to
    | the target platform. This file establishes *where* auth plugs in - the
    | guard/provider structure - and implements no login/logout logic itself;
    | that is a backlog item's job. The 'users' provider below intentionally
    | has no backing App\Models\User class or migration yet: the User entity
    | is owned by MOD-002 and is created by a future backlog item as part of
    | building the actual domain entity, not by this foundation.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            // Deliberately not yet backed by a model file - see note above.
            'model' => App\Models\User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
