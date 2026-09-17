<?php

namespace Database\Seeders;

use App\Models\HeroImage;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class BrandedHeroSlidesSeeder extends Seeder
{
    public const INSTALLATION_KEY = 'branded_hero_slides_20260918_installed';

    public function run(): void
    {
        if (Setting::where('key', self::INSTALLATION_KEY)->exists()) {
            return;
        }

        $slides = [
            [
                'slug' => 'care-coordination',
                'title' => 'Asian Health Connect - Care coordination',
                'heading_bn' => 'চিকিৎসার প্রতিটি ধাপে আপনার পাশে',
                'heading_en' => 'Support at every step of your care journey',
                'body_bn' => 'উপযুক্ত হাসপাতাল ও ডাক্তার খোঁজা থেকে চিকিৎসার পরিকল্পনা পর্যন্ত, আপনার পরিবারের প্রয়োজন বুঝে আমরা সমন্বয় করি।',
                'body_en' => 'From finding a suitable hospital and doctor to planning your visit, we coordinate care around your family\'s needs.',
                'alt_text' => 'Asian Health Connect: a care coordinator helps a Bangladeshi family. AI-generated illustration.',
            ],
            [
                'slug' => 'specialist-appointment',
                'title' => 'Asian Health Connect - Specialist appointments',
                'heading_bn' => 'সঠিক বিশেষজ্ঞ, সহজ অ্যাপয়েন্টমেন্ট',
                'heading_en' => 'The right specialist, a simpler appointment',
                'body_bn' => 'মেডিকেল রিপোর্ট ও চিকিৎসার প্রয়োজন অনুযায়ী উপযুক্ত বিশেষজ্ঞ খুঁজে পেতে এবং অ্যাপয়েন্টমেন্ট নিতে সহায়তা করি।',
                'body_en' => 'We help find a suitable specialist based on your medical reports and care needs, and coordinate your appointment.',
                'alt_text' => 'Asian Health Connect: a doctor discusses care with a patient and his daughter. AI-generated illustration.',
            ],
            [
                'slug' => 'airport-arrival',
                'title' => 'Asian Health Connect - Airport arrival',
                'heading_bn' => 'বিদেশে পৌঁছেই পাশে বিশ্বস্ত সহায়তা',
                'heading_en' => 'A welcoming start when you arrive abroad',
                'body_bn' => 'অপরিচিত দেশে পৌঁছানোর পর এয়ারপোর্ট পিকআপের সমন্বয় করি, যেন রোগী ও পরিবারের যাত্রা আরও স্বস্তির হয়।',
                'body_en' => 'We coordinate airport pickup on arrival so patients and their families can begin their visit with greater peace of mind.',
                'alt_text' => 'Asian Health Connect: an airport coordinator welcomes a family with luggage. AI-generated illustration.',
            ],
            [
                'slug' => 'interpreter-support',
                'title' => 'Asian Health Connect - Interpreter support',
                'heading_bn' => 'ভাষার বাধা পেরিয়ে চিকিৎসায় স্বস্তি',
                'heading_en' => 'Clearer conversations, more comfortable care',
                'body_bn' => 'ডাক্তার ও হাসপাতালের সঙ্গে সহজে কথা বলার জন্য দোভাষীর সংযোগে সহায়তা করি, যাতে রোগী নিজের প্রয়োজন বুঝিয়ে বলতে পারেন।',
                'body_en' => 'We help connect you with an interpreter so you can explain your needs and communicate more easily with doctors and hospital staff.',
                'alt_text' => 'Asian Health Connect: an interpreter supports a family speaking with a doctor. AI-generated illustration.',
            ],
        ];

        // Copy versioned assets before publishing records; a failed copy must not create a broken slide.
        foreach ($slides as $slide) {
            $source = public_path('images/hero-banners-2026-09/'.$slide['slug'].'.png');
            if (! is_file($source) || ! Storage::disk('public')->put($this->imagePath($slide['slug']), file_get_contents($source))) {
                throw new RuntimeException('Could not install hero banner: '.$slide['slug']);
            }
        }

        DB::transaction(function () use ($slides): void {
            HeroImage::query()->increment('sort_order', count($slides));

            // Retire only untouched starter images, keeping custom uploads and their content.
            HeroImage::where('title', 'Sample - Hospital support')->where('image_path', 'hero-images/slider-hospital.jpg')->update(['is_active' => false]);
            HeroImage::where('title', 'Sample - Doctor appointments')->where('image_path', 'hero-images/slider-doctor.jpg')->update(['is_active' => false]);

            foreach ($slides as $index => $slide) {
                $slug = $slide['slug'];
                unset($slide['slug']);
                HeroImage::create($slide + [
                    'image_path' => $this->imagePath($slug),
                    'image_fit' => 'contain',
                    'is_active' => true,
                    'sort_order' => $index,
                ]);
            }

            Setting::create(['key' => self::INSTALLATION_KEY, 'value' => '1']);
        });
    }

    private function imagePath(string $slug): string
    {
        return 'hero-images/brand-2026-09/'.$slug.'.png';
    }
}
