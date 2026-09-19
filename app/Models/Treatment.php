<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Treatment extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'sort_order' => 'integer'];
    }

    public function imageUrl(): string
    {
        if ($this->image_path) {
            return Storage::disk('public')->url($this->image_path);
        }

        $illustration = array_key_exists($this->illustration ?? '', config('treatments.illustrations'))
            ? $this->illustration
            : 'general';

        return asset('images/treatments/'.$illustration.'.webp');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(fn (Builder $query) => $query
            ->where('name', 'like', '%'.$term.'%')
            ->orWhere('name_bn', 'like', '%'.$term.'%')
            ->orWhere('description', 'like', '%'.$term.'%')
            ->orWhere('description_bn', 'like', '%'.$term.'%'));
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(TreatmentCost::class);
    }
}
