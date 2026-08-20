<?php

use Illuminate\Support\Str;

return [
    'default' => env('CACHE_STORE', 'file'),
    'stores' => [
        'database' => [
            'driver' => 'database',
            'table' => env('DB_CACHE_TABLE', 'cache'),
            'lock_connection' => null,
            'lock_table' => env('DB_CACHE_LOCK_TABLE', 'cache_locks'),
        ],
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
    ],
    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_cache_'),
];
