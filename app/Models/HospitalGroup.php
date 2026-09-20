<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class HospitalGroup extends Model
{
    protected $guarded = [];

    public function hospitals(): HasMany
    {
        return $this->hasMany(Hospital::class);
    }

    public function logoUrl(): ?string
    {
        if (filled($this->logo)) {
            return Storage::disk('public')->url($this->logo);
        }

        return filled($this->catalog_logo) ? asset($this->catalog_logo) : null;
    }

    public function logoNeedsDarkBackground(): bool
    {
        // These bundled assets are the hospitals' official reversed (white) wordmarks.
        return blank($this->logo) && in_array($this->catalog_logo, [
            'images/hospital-logos/manipal.webp',
            'images/hospital-logos/aster.webp',
            'images/hospital-logos/gleneagles.webp', 'images/hospital-logos/sri-ramachandra.webp',
        ], true);
    }
}
