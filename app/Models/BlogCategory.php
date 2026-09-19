<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogCategory extends Model
{
    use HasLocalizedContent;

    protected $guarded = [];

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }
}
