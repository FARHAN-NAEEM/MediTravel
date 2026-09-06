<?php

namespace App\Models\Concerns;

use App\Services\SiteContactService;

trait ClearsSiteContactCache
{
    public static function bootClearsSiteContactCache(): void
    {
        static::saved(fn () => app(SiteContactService::class)->forget());
        static::deleted(fn () => app(SiteContactService::class)->forget());
    }
}
