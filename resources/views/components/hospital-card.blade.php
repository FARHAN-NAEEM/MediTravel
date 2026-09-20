@props(['hospital'])
<a href="{{ route('hospitals.show', $hospital) }}" class="hospital-card" data-hospital-card>
    <div class="hospital-card__visual {{ ! $hospital->photoUrl() && $hospital->logoNeedsDarkBackground() ? 'hospital-logo--reversed' : '' }}">
        @if ($hospital->photoUrl())
            <img src="{{ $hospital->photoUrl() }}" alt="{{ $hospital->name }}" class="hospital-card__photo" width="560" height="280" loading="lazy" decoding="async">
        @elseif ($hospital->logoUrl())
            <img src="{{ $hospital->logoUrl() }}" alt="{{ $hospital->group?->name ?? $hospital->name }}" class="hospital-card__logo" width="280" height="140" loading="lazy" decoding="async">
        @else
            <img src="{{ asset('images/hospital-card-illustration.svg') }}" alt="" class="hospital-card__logo" width="480" height="240" loading="lazy" decoding="async">
        @endif
    </div>
    <div class="hospital-card__body">
        <span class="hospital-card__care">{{ __('hospitals.care_types.'.($hospital->care_type ?? 'multi-specialty')) }}</span>
        <h3>{{ $hospital->localized('name') }}</h3>
        <p class="hospital-card__location"><x-heroicon-o-map-pin class="h-4 w-4 shrink-0" /><span>{{ $hospital->locationLabel() }}</span></p>
        @if ($hospital->cardHighlight())<p class="hospital-card__note">{{ $hospital->cardHighlight() }}</p>@endif
        @if ($hospital->accreditation)<p class="hospital-card__accreditation"><x-heroicon-o-shield-check class="h-4 w-4" />{{ $hospital->accreditation }}</p>@endif
        <span class="hospital-card__link">{{ __('hospitals.details') }}<x-heroicon-o-arrow-up-right class="h-4 w-4 shrink-0" /></span>
    </div>
</a>
