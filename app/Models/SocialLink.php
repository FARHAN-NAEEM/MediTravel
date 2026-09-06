<?php

namespace App\Models;

use App\Models\Concerns\ClearsSiteContactCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    use ClearsSiteContactCache;

    public const PLATFORMS = [
        'facebook' => 'Facebook',
        'youtube' => 'YouTube',
        'instagram' => 'Instagram',
        'linkedin' => 'LinkedIn',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
