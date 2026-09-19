<?php

namespace App\Models\Concerns;

trait HasLocalizedContent
{
    public function localized(string $field): ?string
    {
        if (app()->getLocale() === 'bn' && filled($translation = $this->getAttribute($field.'_bn'))) {
            return $translation;
        }

        return $this->getAttribute($field);
    }
}
