@php
    $countryNames = __('home_discovery.hospital_countries');
    $hospitalPanels = $hospitalMarkets->map(fn (array $market) => [
        'key' => 'country-'.$market['key'],
        'label' => $countryNames[$market['name']] ?? $market['name'],
        'hospitals' => $market['country']?->hospitals ?? collect(),
        'url' => route('hospitals.index', ['country' => $market['key']]),
    ])->prepend([
        'key' => 'featured',
        'label' => __('home_discovery.featured_filter'),
        'hospitals' => $featuredHospitals,
        'url' => route('hospitals.index'),
    ]);
    $hospitalPanelLinks = $hospitalPanels->pluck('url', 'key');
    $hospitalPanelTitles = $hospitalPanels->mapWithKeys(fn (array $panel) => [
        $panel['key'] => $panel['key'] === 'featured'
            ? __('site.home.featured_hospitals')
            : __('home_discovery.country_hospitals', ['country' => $panel['label']]),
    ]);
@endphp

<section
    id="featured-hospitals"
    class="featured-hospitals py-14"
    aria-labelledby="featured-hospitals-title"
    x-data="{ activeCountry: 'featured', links: @js($hospitalPanelLinks), titles: @js($hospitalPanelTitles) }"
>
    <div class="container-page">
        <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
            <div class="min-w-0">
                <h2 id="featured-hospitals-title" class="section-title" x-text="titles[activeCountry]">{{ __('site.home.featured_hospitals') }}</h2>
                <p class="readable-copy mt-2 text-slate-600">{{ __('home_discovery.hospitals_subtitle') }}</p>
            </div>
            <a
                href="{{ route('hospitals.index') }}"
                :href="links[activeCountry]"
                :aria-label="@js(__('site.home.view_all')) + ': ' + titles[activeCountry]"
                class="btn-secondary shrink-0 gap-2"
                data-featured-view-all
            >
                {{ __('site.home.view_all') }}
                <x-heroicon-o-arrow-right class="h-4 w-4" />
            </a>
        </div>

        <div class="featured-hospitals__filters" role="group" aria-label="{{ __('home_discovery.hospital_filter_label') }}" x-cloak>
            @foreach ($hospitalPanels as $panel)
                <button
                    type="button"
                    class="featured-hospitals__filter"
                    data-country-filter="{{ $panel['key'] }}"
                    @click="activeCountry = @js($panel['key'])"
                    :aria-pressed="activeCountry === @js($panel['key'])"
                    aria-controls="featured-hospital-results"
                >
                    @if ($panel['key'] === 'featured')
                        <x-heroicon-o-star class="h-4 w-4 shrink-0" aria-hidden="true" />
                    @endif
                    {{ $panel['label'] }}
                </button>
            @endforeach
        </div>

        <div id="featured-hospital-results" class="featured-hospitals__results" aria-live="polite">
            @foreach ($hospitalPanels as $panel)
                <div
                    data-hospital-panel="{{ $panel['key'] }}"
                    x-show="activeCountry === @js($panel['key'])"
                    @if ($panel['key'] !== 'featured') x-cloak style="display: none;" @endif
                >
                    @if ($panel['hospitals']->isNotEmpty())
                        <div class="featured-hospitals__grid">
                            @foreach ($panel['hospitals'] as $hospital)
                                <x-hospital-card :hospital="$hospital" />
                            @endforeach
                        </div>
                    @else
                        <div class="featured-hospitals__empty">
                            <x-heroicon-o-building-office-2 class="h-9 w-9 text-tealTrust" aria-hidden="true" />
                            <p class="font-semibold leading-7 text-navyDeep">
                                {{ $panel['key'] === 'featured' ? __('home_discovery.featured_empty') : __('home_discovery.country_hospitals_empty', ['country' => $panel['label']]) }}
                            </p>
                            <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-tealTrust hover:underline">
                                {{ __('hospitals.talk') }}
                                <x-heroicon-o-arrow-right class="h-4 w-4" />
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
