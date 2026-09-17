<x-layouts.app :title="__('site.home.title')">
    <section class="bg-white">
        <div class="container-page grid min-h-[620px] items-center gap-10 py-12 lg:grid-cols-[1.05fr_.95fr]">
            <div class="w-full min-w-0">
                <div class="mb-4 inline-flex rounded-md bg-tealTrust/10 px-3 py-2 text-sm font-semibold text-tealTrust">{{ __('site.home.eyebrow') }}</div>
                <h1 class="readable-heading max-w-2xl text-3xl font-extrabold leading-[1.35] tracking-normal text-navyDeep sm:text-4xl sm:leading-[1.3] lg:text-5xl lg:leading-[1.25]">{{ __('site.home.headline') }}</h1>
                <p class="readable-copy mt-5 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">{{ __('site.home.subhead') }}</p>
                <form action="{{ route('search') }}" method="GET" class="mt-8 grid gap-3 rounded-lg border border-slate-200 bg-cloud p-3 shadow-sm sm:grid-cols-[1fr_auto]">
                    <input name="q" required minlength="2" maxlength="100" class="rounded-md border-slate-200" placeholder="{{ __('site.home.search_placeholder') }}" aria-label="{{ __('site.home.search_placeholder') }}">
                    <button class="btn-primary">{{ __('site.home.search') }}</button>
                </form>
                <div class="mt-8 grid grid-cols-3 gap-2 sm:gap-4">
                    <div class="min-w-0"><div class="text-xl font-bold text-tealTrust sm:text-3xl">{{ $stats['hospitals'] }}+</div><div class="text-wrap-anywhere text-xs text-slate-500 sm:text-sm">{{ __('site.home.hospitals') }}</div></div>
                    <div class="min-w-0"><div class="text-xl font-bold text-tealTrust sm:text-3xl">{{ $stats['doctors'] }}+</div><div class="text-wrap-anywhere text-xs text-slate-500 sm:text-sm">{{ __('site.home.doctors') }}</div></div>
                    <div class="min-w-0"><div class="text-xl font-bold text-tealTrust sm:text-3xl">{{ $stats['patients'] }}+</div><div class="text-wrap-anywhere text-xs text-slate-500 sm:text-sm">{{ __('site.home.patients_guided') }}</div></div>
                </div>
            </div>
            @include('pages.partials.hero-slider')
        </div>
    </section>

    <section class="overflow-hidden border-y border-slate-200 bg-[#f4f8fa] py-16 md:py-20">
        <div class="container-page">
            @php
                $journeyIcons = [
                    'heroicon-o-magnifying-glass-circle',
                    'heroicon-o-calendar-days',
                    'heroicon-o-map-pin',
                    'heroicon-o-language',
                ];

                $journeyAngles = ['-90deg', '0deg', '90deg', '180deg'];
                $journeyColors = ['#0D7D74', '#2579B8', '#D97706', '#0F2942'];
            @endphp

            <div>
                <div class="mx-auto w-full max-w-[340px] text-center sm:max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-md border border-tealTrust/20 bg-white px-3 py-2 text-sm font-bold text-tealTrust shadow-sm">
                        <x-heroicon-o-shield-check class="h-5 w-5" />
                        <span>{{ __('site.home.journey_kicker') }}</span>
                    </div>
                    <h2 class="readable-heading mt-5 text-2xl font-bold leading-[1.35] text-navyDeep sm:text-3xl md:text-4xl md:leading-[1.3]">{{ __('site.home.journey_title') }}</h2>
                    <p class="readable-copy mt-4 text-base leading-8 text-slate-600 md:text-lg">{{ __('site.home.journey_sub') }}</p>
                    <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                        <a href="{{ route('services.index') }}" class="btn-primary gap-2 px-6 py-3.5">
                            <span>{{ __('site.home.journey_cta') }}</span>
                            <x-heroicon-o-arrow-right class="h-5 w-5" />
                        </a>
                        <a href="{{ route('contact') }}" class="btn-secondary px-6 py-3.5">{{ __('site.home.journey_secondary_cta') }}</a>
                    </div>
                </div>

                <div class="service-orbit mt-12" aria-label="{{ __('site.home.journey_title') }}">
                    <div class="service-orbit__ring" aria-hidden="true"></div>
                    <div class="service-orbit__center">
                        <img
                            src="{{ asset('images/asian-health-connect-logo.png') }}"
                            alt="Asian Health Connect"
                            class="service-orbit__logo"
                            width="2172"
                            height="724"
                            loading="lazy"
                        >
                        <span class="service-orbit__center-label">{{ __('site.home.journey_center') }}</span>
                    </div>

                    <ol class="service-orbit__items">
                        @foreach (__('site.home.journey_steps') as $step)
                            <li
                                class="service-orbit__item"
                                style="--angle: {{ $journeyAngles[$loop->index] }}; --accent-color: {{ $journeyColors[$loop->index] }};"
                            >
                                <span class="service-orbit__badge">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="service-orbit__icon">
                                    <x-dynamic-component :component="$journeyIcons[$loop->index]" class="h-6 w-6" />
                                </span>
                                <span class="service-orbit__copy">
                                    <strong>{{ $step['title'] }}</strong>
                                    <span>{{ $step['body'] }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="py-14">
        <div class="container-page">
            <div class="mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <h2 class="section-title">{{ __('site.home.featured_hospitals') }}</h2>
                    <p class="mt-2 text-slate-600">{{ __('site.home.featured_hospitals_sub') }}</p>
                </div>
                <a href="{{ route('hospitals.index') }}" class="btn-secondary shrink-0">{{ __('site.home.view_all') }}</a>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($featuredHospitals as $hospital)
                    <a href="{{ route('hospitals.show', $hospital) }}" class="group card flex h-full min-w-0 flex-col overflow-hidden transition duration-300 hover:-translate-y-1 hover:border-tealTrust/30 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-tealTrust focus:ring-offset-2">
                        <div class="relative h-44 overflow-hidden border-b border-tealTrust/10 bg-[#edf7f6]">
                            <div class="absolute inset-x-0 top-0 h-1 bg-tealTrust"></div>
                            <img
                                src="{{ asset('images/hospital-card-illustration.svg') }}"
                                alt=""
                                aria-hidden="true"
                                width="480"
                                height="240"
                                loading="lazy"
                                decoding="async"
                                class="h-full w-full object-contain p-4 transition duration-300 group-hover:scale-[1.03]"
                            >
                            <span class="absolute bottom-3 right-3 grid h-9 w-9 place-items-center rounded-md border border-white/80 bg-white/95 text-tealTrust shadow-sm transition group-hover:bg-tealTrust group-hover:text-white" aria-hidden="true">
                                <x-heroicon-o-arrow-up-right class="h-4 w-4" />
                            </span>
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <h3 class="readable-heading text-lg font-bold leading-7 text-navyDeep transition group-hover:text-tealTrust">{{ $hospital->name }}</h3>
                            <div class="mt-2 flex items-start gap-2 text-sm text-slate-600">
                                <x-heroicon-o-map-pin class="mt-0.5 h-4 w-4 shrink-0 text-tealTrust" />
                                <span>{{ $hospital->city->name }}, {{ $hospital->country->name }}</span>
                            </div>
                            @if (filled($hospital->accreditation))
                                <div class="mt-auto flex items-start gap-2 pt-4 text-sm font-medium text-tealTrust">
                                    <x-heroicon-o-shield-check class="mt-0.5 h-4 w-4 shrink-0" />
                                    <span>{{ $hospital->accreditation }}</span>
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="overflow-hidden bg-white py-16 md:py-20">
        <div class="container-page">
            <div class="mx-auto max-w-3xl text-center">
                <p class="text-sm font-bold uppercase text-tealTrust">{{ __('site.home.partner_kicker') }}</p>
                <h2 class="readable-heading mt-3 text-3xl font-bold leading-[1.35] text-navyDeep md:text-4xl md:leading-[1.3]">{{ __('site.home.partner_title') }}</h2>
                <p class="readable-copy mt-4 text-base leading-8 text-slate-600 md:text-lg">{{ __('site.home.partner_sub') }}</p>
            </div>

            @php
                $partnerCountryData = $partnerMarkets->map(function (array $market) {
                    $country = $market['country'];

                    return [
                        'key' => $market['key'],
                        'label' => __('site.home.partner_countries.'.$market['key']),
                        'hospitals' => $country?->hospitals->map(function ($hospital) use ($country) {
                            $initials = collect(preg_split('/\s+/', trim($hospital->name)))
                                ->filter()
                                ->take(2)
                                ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
                                ->implode('');

                            return [
                                'name' => $hospital->name,
                                'location' => collect([$hospital->city?->name, $country->name])->filter()->implode(', '),
                                'initials' => $initials,
                                'url' => route('hospitals.show', $hospital),
                            ];
                        })->values()->all() ?? [],
                    ];
                })->values();

                $defaultPartnerHospitals = $partnerCountryData
                    ->map(fn (array $market) => $market['hospitals'][0] ?? null)
                    ->filter()
                    ->values();
            @endphp

            <div
                x-data="{
                    activeCountry: 'all',
                    markets: @js($partnerCountryData),
                    defaultHospitals: @js($defaultPartnerHospitals),
                    get visibleHospitals() {
                        if (this.activeCountry === 'all') return this.defaultHospitals;
                        return this.markets.find((market) => market.key === this.activeCountry)?.hospitals ?? [];
                    },
                    get activeLabel() {
                        return this.markets.find((market) => market.key === this.activeCountry)?.label ?? '';
                    },
                }"
                class="mt-8"
            >
                <div class="flex flex-wrap justify-center gap-2" role="group" aria-label="{{ __('site.home.partner_kicker') }}">
                    <button
                        type="button"
                        data-country-filter="all"
                        @click="activeCountry = 'all'"
                        :aria-pressed="activeCountry === 'all'"
                        :class="activeCountry === 'all' ? 'border-tealTrust bg-tealTrust text-white shadow-sm' : 'border-tealTrust/15 bg-white text-navyDeep hover:border-tealTrust/50 hover:bg-tealTrust/[.04]'"
                        class="inline-flex min-h-10 items-center gap-2 rounded-md border px-4 py-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-tealTrust/30"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-accent"></span>
                        {{ __('site.home.partner_countries.all') }}
                    </button>
                    @foreach ($partnerMarkets as $market)
                        <button
                            type="button"
                            data-country-filter="{{ $market['key'] }}"
                            @click="activeCountry = '{{ $market['key'] }}'"
                            :aria-pressed="activeCountry === '{{ $market['key'] }}'"
                            :class="activeCountry === '{{ $market['key'] }}' ? 'border-tealTrust bg-tealTrust text-white shadow-sm' : 'border-tealTrust/15 bg-white text-navyDeep hover:border-tealTrust/50 hover:bg-tealTrust/[.04]'"
                            class="inline-flex min-h-10 items-center gap-2 rounded-md border px-4 py-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-tealTrust/30"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-accent"></span>
                            {{ __('site.home.partner_countries.'.$market['key']) }}
                        </button>
                    @endforeach
                </div>

                <div x-cloak x-show="visibleHospitals.length > 0" class="mt-10 grid border-l border-t border-slate-200 sm:grid-cols-2 lg:grid-cols-5">
                    <template x-for="hospital in visibleHospitals" :key="hospital.url">
                        <a :href="hospital.url" class="group flex min-h-28 items-center gap-4 border-b border-r border-slate-200 bg-white p-5 transition hover:bg-cloud">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-md bg-navyDeep text-sm font-extrabold text-white transition group-hover:bg-tealTrust" x-text="hospital.initials"></span>
                            <span class="min-w-0">
                                <span class="block font-bold leading-6 text-navyDeep" x-text="hospital.name"></span>
                                <span class="mt-1 block text-sm text-slate-500" x-text="hospital.location"></span>
                            </span>
                        </a>
                    </template>
                </div>

                <p x-cloak x-show="visibleHospitals.length === 0" class="mt-10 text-center text-slate-500">
                    <span x-show="activeCountry === 'all'">{{ __('site.home.partner_empty') }}</span>
                    <span x-show="activeCountry !== 'all'" x-text="@js(__('site.home.partner_country_empty', ['country' => '__COUNTRY__'])).replace('__COUNTRY__', activeLabel)"></span>
                </p>
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('hospitals.index') }}" class="btn-secondary">{{ __('site.home.partner_link') }}</a>
            </div>
        </div>
    </section>

    <section class="py-14">
        <div class="container-page grid gap-8 lg:grid-cols-[.8fr_1.2fr]">
            <div>
                <h2 class="section-title">{{ __('site.home.quick_inquiry') }}</h2>
                <p class="mt-3 text-slate-600">{{ __('site.home.quick_inquiry_sub') }}</p>
            </div>
            @include('pages.partials.inquiry-form', ['type' => 'appointment'])
        </div>
    </section>
</x-layouts.app>
