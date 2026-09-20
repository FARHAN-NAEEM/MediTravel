<a href="{{ route('hospitals.index', array_filter(array_merge($filters, ['group' => $group->slug]))) }}#hospital-results" class="hospital-brand {{ ($filters['group'] ?? '') === $group->slug ? 'hospital-brand--active' : '' }}" @if(($filters['group'] ?? '') === $group->slug) aria-current="true" @endif>
    @if ($group->logoUrl())<img src="{{ $group->logoUrl() }}" alt="" class="{{ $group->logoNeedsDarkBackground() ? 'hospital-logo--reversed' : '' }}" width="130" height="48" loading="lazy">@else<x-heroicon-o-building-office-2 class="h-8 w-8 text-tealTrust" />@endif
    <span>{{ $group->name }}</span>
</a>
