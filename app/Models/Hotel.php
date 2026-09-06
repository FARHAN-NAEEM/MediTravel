<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class Hotel extends Model
{
    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'cost_min' => 'decimal:2',
            'cost_max' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Hotel $hotel): void {
            $cityIsValid = City::query()
                ->whereKey($hotel->city_id)
                ->where('country_id', $hotel->country_id)
                ->exists();

            if (! $cityIsValid) {
                throw ValidationException::withMessages([
                    'city_id' => 'The selected city does not belong to the selected country.',
                ]);
            }

            if ($hotel->cost_min !== null && $hotel->cost_max !== null && (float) $hotel->cost_min > (float) $hotel->cost_max) {
                throw ValidationException::withMessages([
                    'cost_max' => 'Maximum estimated cost must be greater than or equal to minimum cost.',
                ]);
            }
        });
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function hospitals(): BelongsToMany
    {
        return $this->belongsToMany(Hospital::class, 'hotel_hospital');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(HotelRoom::class);
    }

    public function bookingRequests(): HasMany
    {
        return $this->hasMany(HotelBookingRequest::class);
    }

    public function imageUrls(): array
    {
        return collect($this->images ?? [])
            ->map(fn (string $path): string => Storage::disk('public')->url($path))
            ->values()
            ->all();
    }

    public function estimatedCostLabel(): ?string
    {
        if ($this->cost_min === null || $this->cost_max === null) {
            return null;
        }

        return number_format((float) $this->cost_min, 0).' – '.number_format((float) $this->cost_max, 0).' '.$this->currency.' / '.str($this->pricing_unit)->headline();
    }
}
