<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotelRoom extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'facilities' => 'array',
            'has_ac' => 'boolean',
            'has_wifi' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function bookingRequests(): HasMany
    {
        return $this->hasMany(HotelBookingRequest::class);
    }
}
