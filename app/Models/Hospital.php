<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Hospital extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];

    public const CARE_TYPES = ['multi-specialty', 'cardiac', 'cancer', 'fertility', 'eye', 'kidney', 'ent', 'women-children'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(HospitalGroup::class, 'hospital_group_id');
    }

    public function logoUrl(): ?string
    {
        return filled($this->logo) ? Storage::disk('public')->url($this->logo) : $this->group?->logoUrl();
    }

    public function photoUrl(): ?string
    {
        $photo = $this->images[0] ?? null;

        return filled($photo) ? Storage::disk('public')->url($photo) : null;
    }

    public function logoNeedsDarkBackground(): bool
    {
        return blank($this->logo) && ($this->group?->logoNeedsDarkBackground() ?? false);
    }

    public function locationLabel(): string
    {
        $names = trans('hospitals.locations');

        return collect([$this->city?->name, $this->country?->name])->filter()
            ->map(fn ($name) => $names[$name] ?? $name)->implode(', ');
    }

    public function cardHighlight(): ?string
    {
        $localized = app()->getLocale() === 'bn'
            ? $this->card_highlight_bn
            : $this->card_highlight_en;
        $fallback = app()->getLocale() === 'bn'
            ? $this->card_highlight_en
            : $this->card_highlight_bn;

        return filled($localized)
            ? trim($localized)
            : (filled($fallback) ? trim($fallback) : null);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_featured' => 'boolean',
            'directory_reviewed_at' => 'date',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }

    public function treatmentCosts(): HasMany
    {
        return $this->hasMany(TreatmentCost::class);
    }

    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class, 'hotel_hospital');
    }
}
