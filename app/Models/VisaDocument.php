<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisaDocument extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function localizedTitle(): string
    {
        if (app()->getLocale() === 'en' && $this->title_en) {
            return $this->title_en;
        }

        return $this->title_bn;
    }

    public function localizedDescription(): ?string
    {
        if (app()->getLocale() === 'en' && $this->description_en) {
            return $this->description_en;
        }

        return $this->description_bn;
    }
}
