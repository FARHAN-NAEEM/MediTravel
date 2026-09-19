@props(['treatment'])

<a href="{{ route('treatments.show', $treatment) }}" class="treatment-card" data-treatment-card>
    <div class="treatment-card__image">
        <img src="{{ $treatment->imageUrl() }}" alt="" width="512" height="512" loading="lazy" decoding="async">
    </div>
    <div class="treatment-card__body">
        <p class="treatment-card__specialty">{{ $treatment->department?->localized('name') }}</p>
        <h2 class="treatment-card__title">{{ $treatment->localized('name') }}</h2>
        <p class="treatment-card__summary">{{ $treatment->localized('description') }}</p>
        <span class="treatment-card__link">
            {{ __('treatments.details') }}
            <x-heroicon-o-arrow-up-right class="h-4 w-4 shrink-0" aria-hidden="true" />
        </span>
    </div>
</a>
