<?php

namespace Database\Seeders;

use App\Models\HeroImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HeroSliderSeeder extends Seeder
{
    public function run(): void
    {
        if (HeroImage::exists()) {
            return;
        }

        $slides = [
            [
                'title' => 'Sample - Hospital support',
                'image_path' => 'hero-images/slider-hospital.jpg',
                'alt_text' => 'Hospital building - illustrative photo',
                'heading_bn' => 'আপনার চিকিৎসার জন্য উপযুক্ত হাসপাতাল',
                'heading_en' => 'The right hospital for your care',
                'body_bn' => 'রোগীর প্রয়োজন অনুযায়ী হাসপাতাল খোঁজা থেকে অ্যাপয়েন্টমেন্টের সমন্বয় পর্যন্ত, প্রতিটি ধাপে পাশে আছে এশিয়ান হেলথ কানেক্ট।',
                'body_en' => 'From finding a suitable hospital to coordinating your appointment, Asian Health Connect supports each step of your care journey.',
                'image_fit' => 'cover',
                'sort_order' => 1,
            ],
            [
                'title' => 'Sample - Doctor appointments',
                'image_path' => 'hero-images/slider-doctor.jpg',
                'alt_text' => 'Doctor with a stethoscope - illustrative photo',
                'heading_bn' => 'সঠিক বিশেষজ্ঞের সঙ্গে চিকিৎসার শুরু',
                'heading_en' => 'Start with the right specialist',
                'body_bn' => 'মেডিকেল রিপোর্ট ও চিকিৎসার প্রয়োজন বুঝে উপযুক্ত বিশেষজ্ঞ খুঁজে পেতে এবং ডাক্তারের অ্যাপয়েন্টমেন্ট নিতে আমরা সহায়তা করি।',
                'body_en' => 'We help you find a suitable specialist based on your medical reports and care needs, and coordinate your doctor appointment.',
                'image_fit' => 'contain',
                'sort_order' => 2,
            ],
        ];

        DB::transaction(function () use ($slides): void {
            foreach ($slides as $slide) {
                Storage::disk('public')->put($slide['image_path'], file_get_contents(public_path('images/'.basename($slide['image_path']))));
                HeroImage::create($slide);
            }
        });
    }
}
