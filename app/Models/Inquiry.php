<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inquiry extends Model
{
    protected $guarded = [];

    public static function nextReference(): string
    {
        return 'MT-'.now()->format('ymd').'-'.str_pad((string) (self::query()->whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);
    }

    public static function statusOptions(): array
    {
        return [
            'new' => 'New',
            'contacted' => 'Contacted',
            'documents_pending' => 'Documents pending',
            'hospital_sent' => 'Sent to hospital',
            'appointment_confirmed' => 'Appointment confirmed',
            'closed' => 'Closed',
        ];
    }

    public static function statusLabel(?string $status): string
    {
        return self::statusOptions()[$status] ?? str($status ?? '')->headline()->toString();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabel($this->status);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(InquiryLog::class);
    }
}
