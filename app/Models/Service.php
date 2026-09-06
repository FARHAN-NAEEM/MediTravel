<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function requiredDocuments(): HasMany
    {
        return $this->hasMany(VisaDocument::class)
            ->where('is_active', true)
            ->where('is_required', true)
            ->orderBy('sort_order')
            ->orderBy('title_bn');
    }
}
