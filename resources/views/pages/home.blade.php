<x-layouts.app :title="__('site.home.title')">
    <section class="bg-white">
        <div class="container-page grid min-h-[620px] items-center gap-10 py-12 lg:grid-cols-[1.05fr_.95fr]">
            <div class="w-full min-w-0">
                <div class="mb-4 inline-flex rounded-md bg-tealTrust/10 px-3 py-2 text-sm font-semibold text-tealTrust">{{ __('site.home.eyebrow') }}</div>
                <h1 class="readable-heading max-w-2xl text-3xl font-extrabold leading-[1.35] tracking-normal text-navyDeep sm:text-4xl sm:leading-[1.3] lg:text-5xl lg:leading-[1.25]">{{ __('site.home.headline') }}</h1>
                <p class="readable-copy mt-5 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">{{ __('home_discovery.hero_subhead') }}</p>
                <form action="{{ route('search') }}" method="GET" class="mt-8 grid gap-3 rounded-lg border border-slate-200 bg-cloud p-3 shadow-sm sm:grid-cols-[1fr_auto]">
                    <input name="q" required minlength="2" maxlength="100" class="rounded-md border-slate-200" placeholder="{{ __('site.home.search_placeholder') }}" aria-label="{{ __('site.home.search_placeholder') }}">
                    <button class="btn-primary">{{ __('site.home.search') }}</button>
                </form>
                <div class="mt-8 grid grid-cols-3 gap-2 sm:gap-4">
                    <div class="min-w-0"><div class="text-xl font-bold text-tealTrust sm:text-3xl">{{ number_format($stats['hospitals']) }}</div><div class="text-wrap-anywhere text-xs text-slate-500 sm:text-sm">{{ __('site.home.hospitals') }}</div></div>
                    <div class="min-w-0"><div class="text-xl font-bold text-tealTrust sm:text-3xl">{{ number_format($stats['doctors']) }}</div><div class="text-wrap-anywhere text-xs text-slate-500 sm:text-sm">{{ __('site.home.doctors') }}</div></div>
                    <div class="min-w-0" data-home-stat="destinations"><div class="text-xl font-bold text-tealTrust sm:text-3xl">{{ number_format($stats['destinations']) }}</div><div class="text-wrap-anywhere text-xs text-slate-500 sm:text-sm">{{ __('home_discovery.destinations') }}</div></div>
                </div>
            </div>
            @include('pages.partials.hero-slider')
        </div>
    </section>

    <section class="bg-white py-12 md:py-14" aria-labelledby="home-pathways-title">
        <div class="container-page">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                <div>
                    <h2 id="home-pathways-title" class="section-title">{{ __('home_discovery.title') }}</h2>
                    <p class="readable-copy mt-2 text-slate-600">{{ __('home_discovery.subtitle') }}</p>
                </div>
                <a href="{{ route('services.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-tealTrust hover:underline">
                    {{ __('home_discovery.all_services') }}
                    <x-heroicon-o-arrow-right class="h-4 w-4" />
                </a>
            </div>
            @php
                $discoveryPaths = [
                    ['key' => 'doctors', 'url' => route('doctors.index'), 'icon' => 'heroicon-o-user-group'],
                    ['key' => 'hospitals', 'url' => route('hospitals.index'), 'icon' => 'heroicon-o-building-office-2'],
                    ['key' => 'cost', 'url' => route('cost-estimator.index'), 'icon' => 'heroicon-o-banknotes'],
                    ['key' => 'visa', 'url' => route('visa-support'), 'icon' => 'heroicon-o-document-check'],
                ];
            @endphp
            <div class="mt-7 grid gap-x-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($discoveryPaths as $path)
                    <a href="{{ $path['url'] }}" class="group flex min-w-0 items-start gap-3 border-b border-slate-200 py-4 transition hover:text-tealTrust focus:outline-none focus:ring-2 focus:ring-tealTrust focus:ring-offset-2 sm:border-b-0 sm:border-l sm:pl-5 lg:pl-6">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-md bg-tealTrust/10 text-tealTrust transition group-hover:bg-tealTrust group-hover:text-white">
                            <x-dynamic-component :component="$path['icon']" class="h-6 w-6" />
                        </span>
                        <span class="min-w-0">
                            <strong class="text-wrap-anywhere block font-bold leading-6 text-navyDeep group-hover:text-tealTrust">{{ __('home_discovery.paths.'.$path['key'].'.title') }}</strong>
                            <span class="readable-copy mt-1 block text-sm leading-6 text-slate-600">{{ __('home_discovery.paths.'.$path['key'].'.description') }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
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

    @include('pages.partials.featured-hospitals')

    <section class="border-y border-slate-200 bg-[#f4f8fa] py-14 md:py-16" aria-labelledby="home-questions-title">
        <div class="container-page grid gap-8 lg:grid-cols-[.8fr_1.2fr] lg:gap-14">
            <div>
                <h2 id="home-questions-title" class="section-title">{{ __('home_discovery.questions_title') }}</h2>
                <p class="readable-copy mt-3 leading-7 text-slate-600">{{ __('home_discovery.questions_subtitle') }}</p>
                <a href="{{ route('faq') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-tealTrust hover:underline">
                    {{ __('home_discovery.more_questions') }}
                    <x-heroicon-o-arrow-right class="h-4 w-4" />
                </a>
            </div>
            <div class="border-t border-slate-200">
                @foreach (__('home_discovery.questions') as $item)
                    <details class="group border-b border-slate-200 py-4">
                        <summary class="flex cursor-pointer list-none items-start justify-between gap-4 font-semibold leading-7 text-navyDeep marker:hidden focus:outline-none focus-visible:ring-2 focus-visible:ring-tealTrust">
                            <span>{{ $item['question'] }}</span>
                            <x-heroicon-o-chevron-down class="mt-1 h-5 w-5 shrink-0 text-tealTrust transition group-open:rotate-180" />
                        </summary>
                        <p class="readable-copy pb-1 pt-3 text-sm leading-7 text-slate-600">{{ $item['answer'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-14">
        <div class="container-page grid gap-8 lg:grid-cols-[.8fr_1.2fr]">
            <div>
                <h2 class="section-title">{{ __('site.home.quick_inquiry') }}</h2>
                <p class="mt-3 text-slate-600">{{ __('home_discovery.inquiry_subtitle') }}</p>
            </div>
            @include('pages.partials.inquiry-form', ['type' => 'appointment'])
        </div>
    </section>
</x-layouts.app>
