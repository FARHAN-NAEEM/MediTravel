<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\User;
use App\Observers\UserObserver;
use App\Services\SiteContactService;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        User::observe(UserObserver::class);

        Password::defaults(fn () => Password::min(12)->mixedCase()->numbers()->symbols());

        View::composer('*', function ($view): void {
            $settings = cache()->remember('site_settings', 300, function () {
                if (! class_exists(Setting::class) || ! Schema::hasTable('settings')) {
                    return collect();
                }

                return Setting::query()->pluck('value', 'key');
            });

            $view->with('siteSettings', $settings);
            $view->with(app(SiteContactService::class)->footerData());
        });
    }
}
