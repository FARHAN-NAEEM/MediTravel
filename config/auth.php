<?php

use App\Models\ReferralAgent;
use App\Models\User;

return [
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],
    'guards' => [
        'agent' => ['driver' => 'session', 'provider' => 'referral_agents'],
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],
    'providers' => [
        'referral_agents' => ['driver' => 'eloquent', 'model' => ReferralAgent::class],
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],
    ],
    'passwords' => [
        'agents' => ['provider' => 'referral_agents', 'table' => 'agent_password_reset_tokens', 'expire' => 60, 'throttle' => 60],
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],
    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
