<?php

namespace Database\Seeders;

use App\Models\ContactChannel;
use App\Models\OfficeLocation;
use App\Models\SocialLink;
use Illuminate\Database\Seeder;

class SiteContactSeeder extends Seeder
{
    public function run(): void
    {
        ContactChannel::query()->updateOrCreate(
            ['type' => 'phone', 'value' => env('SUPPORT_PHONE', '+8801700000000')],
            ['label' => 'Main Office', 'is_primary' => true, 'is_active' => true, 'sort_order' => 10],
        );

        ContactChannel::query()->updateOrCreate(
            ['type' => 'whatsapp', 'value' => env('WHATSAPP_NUMBER', '8801700000000')],
            ['label' => 'Patient Support', 'is_primary' => true, 'is_active' => true, 'sort_order' => 10],
        );

        ContactChannel::query()->updateOrCreate(
            ['type' => 'email', 'value' => env('SUPPORT_EMAIL', 'care@asianhealthconnect.com')],
            ['label' => 'Support', 'is_primary' => true, 'is_active' => true, 'sort_order' => 10],
        );

        foreach ([
            ['Head Office', 'Dhaka', 'Dhaka, Bangladesh', 10],
            ['Chattogram Office', 'Chattogram', 'Chattogram, Bangladesh', 20],
            ['Sylhet Office', 'Sylhet', 'Sylhet, Bangladesh', 30],
        ] as [$name, $district, $address, $sortOrder]) {
            OfficeLocation::query()->updateOrCreate(
                ['name' => $name],
                [
                    'district' => $district,
                    'address' => $address,
                    'phone' => null,
                    'map_url' => null,
                    'is_active' => true,
                    'sort_order' => $sortOrder,
                ],
            );
        }

        $facebookUrl = trim((string) env('FACEBOOK_URL'));
        SocialLink::query()->updateOrCreate(
            ['platform' => 'facebook', 'name' => 'Asian Health Connect'],
            [
                'url' => $facebookUrl !== '' ? $facebookUrl : 'https://www.facebook.com/',
                'is_active' => $facebookUrl !== '',
                'sort_order' => 10,
            ],
        );
    }
}
