<?php

namespace App\Services;

use App\Models\ContactChannel;
use App\Models\OfficeLocation;
use App\Models\Setting;
use App\Models\SocialLink;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteContactService
{
    private const CACHE_KEY = 'site_contact_footer_data';

    /** @return array<string, mixed> */
    public function footerData(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function (): array {
            $channels = Schema::hasTable('contact_channels')
                ? ContactChannel::query()->active()->ordered()->get()
                : collect();

            return [
                'footerPhones' => $channels->where('type', 'phone')->values(),
                'footerWhatsapps' => $channels->where('type', 'whatsapp')->values(),
                'footerEmails' => $channels->where('type', 'email')->values(),
                'footerOffices' => Schema::hasTable('office_locations')
                    ? OfficeLocation::query()->active()->ordered()->get()
                    : collect(),
                'footerSocials' => Schema::hasTable('social_links')
                    ? SocialLink::query()->active()->ordered()->get()
                    : collect(),
                'primaryWhatsapp' => $channels
                    ->where('type', 'whatsapp')
                    ->firstWhere('is_primary', true)
                    ?? $channels->firstWhere('type', 'whatsapp'),
            ];
        });
    }

    public function primaryWhatsappNumber(): string
    {
        /** @var ContactChannel|null $primaryWhatsapp */
        $primaryWhatsapp = $this->footerData()['primaryWhatsapp'];
        $value = $primaryWhatsapp?->value;

        if (! $value && Schema::hasTable('settings')) {
            $value = Setting::query()->where('key', 'whatsapp_number')->value('value');
        }

        $value ??= (string) config('services.whatsapp.number', '8801700000000');

        return preg_replace('/\D+/', '', $value) ?: '8801700000000';
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
