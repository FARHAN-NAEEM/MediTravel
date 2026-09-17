<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HeroImage extends Model
{
    protected $guarded = [];

    protected $attributes = ['image_fit' => 'contain'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function imageUrl(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }

    public function localizedContent(string $field): ?string
    {
        $locale = app()->getLocale() === 'en' ? 'en' : 'bn';
        $fallback = $locale === 'en' ? 'bn' : 'en';

        return $this->getAttribute($field.'_'.$locale) ?: $this->getAttribute($field.'_'.$fallback);
    }

    public static function slideInterval(): int
    {
        $seconds = (int) Setting::where('key', 'hero_slide_interval')->value('value');

        return in_array($seconds, [3, 5], true) ? $seconds : 5;
    }
}
