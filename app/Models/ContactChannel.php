<?php

namespace App\Models;

use App\Models\Concerns\ClearsSiteContactCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContactChannel extends Model
{
    use ClearsSiteContactCache;

    public const TYPES = [
        'phone' => 'Phone',
        'whatsapp' => 'WhatsApp',
        'email' => 'Email',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ContactChannel $channel): void {
            if (! $channel->is_active) {
                $channel->is_primary = false;
            }
        });

        static::saved(function (ContactChannel $channel): void {
            if (! $channel->is_primary) {
                return;
            }

            static::query()
                ->where('type', $channel->type)
                ->whereKeyNot($channel->getKey())
                ->where('is_primary', true)
                ->update(['is_primary' => false, 'updated_at' => now()]);
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function normalizedNumber(): string
    {
        return preg_replace('/\D+/', '', $this->value) ?? '';
    }

    public function destinationUrl(): string
    {
        return match ($this->type) {
            'email' => 'mailto:'.$this->value,
            'whatsapp' => 'https://wa.me/'.$this->normalizedNumber(),
            default => 'tel:'.(preg_replace('/[^0-9+]/', '', $this->value) ?? ''),
        };
    }
}
