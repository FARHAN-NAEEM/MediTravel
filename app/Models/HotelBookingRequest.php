<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelBookingRequest extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
        ];
    }

    public static function nextReference(): string
    {
        return 'HB-'.now()->format('ymd').'-'.str_pad((string) (self::query()->whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);
    }

    public static function statusOptions(): array
    {
        return [
            'new' => 'New',
            'contacted' => 'Contacted',
            'quote_shared' => 'Estimate shared',
            'confirmed' => 'Booking confirmed',
            'closed' => 'Closed',
            'cancelled' => 'Cancelled',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class, 'hotel_room_id');
    }
}
