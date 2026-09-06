<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $description ?? __('site.meta_description') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    x-data="{ mobileNavOpen: false }"
    @keydown.escape.window="mobileNavOpen = false"
>
    @php
        $whatsapp = $primaryWhatsapp?->normalizedNumber()
            ?: preg_replace('/\D+/', '', $siteSettings['whatsapp_number'] ?? config('services.whatsapp.number', '8801700000000'));
        $currentLocale = app()->getLocale();
    @endphp

    <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
        <div class="container-page flex h-16 items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center" aria-label="Asian Health Connect home">
                <img
                    src="{{ asset('images/asian-health-connect-logo.png') }}"
                    alt="Asian Health Connect"
                    class="h-11 w-auto max-w-[190px] object-contain sm:max-w-[230px]"
                    width="2172"
                    height="724"
                >
            </a>
            <nav class="hidden items-center gap-6 text-sm font-semibold text-slate-700 xl:flex">
                <a href="{{ route('doctors.index') }}" class="hover:text-tealTrust">{{ __('site.nav.doctors') }}</a>
                <a href="{{ route('hospitals.index') }}" class="hover:text-tealTrust">{{ __('site.nav.hospitals') }}</a>
                <a href="{{ route('hotels.index') }}" class="hover:text-tealTrust">{{ __('site.nav.hotel_booking') }}</a>
                <a href="{{ route('treatments.index') }}" class="hover:text-tealTrust">{{ __('site.nav.treatments') }}</a>
                <a href="{{ route('cost-estimator.index') }}" class="hover:text-tealTrust">{{ __('site.nav.cost') }}</a>
                <a href="{{ route('visa-support') }}" class="hover:text-tealTrust">{{ __('site.nav.visa') }}</a>
                <a href="{{ route('track.index') }}" class="hover:text-tealTrust">{{ __('site.nav.track') }}</a>
            </nav>
            <div class="flex items-center gap-2">
                <div class="hidden overflow-hidden rounded-md border border-slate-200 text-sm font-semibold sm:inline-flex">
                    <a href="{{ route('language.switch', 'bn') }}" class="px-3 py-2 {{ $currentLocale === 'bn' ? 'bg-tealTrust text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">বাংলা</a>
                    <a href="{{ route('language.switch', 'en') }}" class="px-3 py-2 {{ $currentLocale === 'en' ? 'bg-tealTrust text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">English</a>
                </div>
                <a href="https://wa.me/{{ $whatsapp }}" class="btn-primary hidden py-2 sm:inline-flex">{{ __('site.nav.whatsapp') }}</a>
                <button
                    type="button"
                    class="grid h-10 w-10 place-items-center rounded-md border border-slate-200 bg-white text-navyDeep transition hover:border-tealTrust hover:text-tealTrust focus:outline-none focus:ring-2 focus:ring-tealTrust/30 xl:hidden"
                    @click="mobileNavOpen = ! mobileNavOpen"
                    :aria-expanded="mobileNavOpen.toString()"
                    aria-controls="mobile-navigation"
                    :aria-label="mobileNavOpen ? '{{ __('site.nav.close_menu') }}' : '{{ __('site.nav.open_menu') }}'"
                    :title="mobileNavOpen ? '{{ __('site.nav.close_menu') }}' : '{{ __('site.nav.open_menu') }}'"
                >
                    <x-heroicon-o-bars-3 x-show="! mobileNavOpen" class="h-6 w-6" />
                    <x-heroicon-o-x-mark x-cloak x-show="mobileNavOpen" class="h-6 w-6" />
                </button>
            </div>
        </div>

        <div
            id="mobile-navigation"
            x-cloak
            x-show="mobileNavOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="-translate-y-2 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="-translate-y-2 opacity-0"
            @click.outside="mobileNavOpen = false"
            class="absolute inset-x-0 top-full max-h-[calc(100vh-4rem)] overflow-y-auto border-b border-tealTrust/15 bg-white shadow-xl xl:hidden"
        >
            <div class="container-page py-4 sm:py-5">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase text-tealTrust">Asian Health Connect</p>
                        <h2 class="mt-1 text-base font-bold text-navyDeep">{{ __('site.nav.mobile_title') }}</h2>
                    </div>
                    <span class="grid h-9 w-9 place-items-center rounded-md bg-tealTrust/10 text-tealTrust">
                        <x-heroicon-o-squares-2x2 class="h-5 w-5" />
                    </span>
                </div>

                <nav aria-label="{{ __('site.nav.mobile_title') }}" class="grid gap-2 sm:grid-cols-2">
                    <a href="{{ route('home') }}" @click="mobileNavOpen = false" class="group flex min-h-14 items-center gap-3 rounded-md border px-3 py-2.5 transition {{ request()->routeIs('home') ? 'border-tealTrust/30 bg-tealTrust/10 text-tealTrust' : 'border-slate-200 text-navyDeep hover:border-tealTrust/30 hover:bg-tealTrust/5' }}">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md bg-cloud text-tealTrust"><x-heroicon-o-home class="h-5 w-5" /></span>
                        <span class="font-semibold">{{ __('site.nav.home') }}</span>
                    </a>
                    <a href="{{ route('doctors.index') }}" @click="mobileNavOpen = false" class="group flex min-h-14 items-center gap-3 rounded-md border px-3 py-2.5 transition {{ request()->routeIs('doctors.*') ? 'border-tealTrust/30 bg-tealTrust/10 text-tealTrust' : 'border-slate-200 text-navyDeep hover:border-tealTrust/30 hover:bg-tealTrust/5' }}">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md bg-cloud text-tealTrust"><x-heroicon-o-user-group class="h-5 w-5" /></span>
                        <span class="font-semibold">{{ __('site.nav.doctors') }}</span>
                    </a>
                    <a href="{{ route('hospitals.index') }}" @click="mobileNavOpen = false" class="group flex min-h-14 items-center gap-3 rounded-md border px-3 py-2.5 transition {{ request()->routeIs('hospitals.*') ? 'border-tealTrust/30 bg-tealTrust/10 text-tealTrust' : 'border-slate-200 text-navyDeep hover:border-tealTrust/30 hover:bg-tealTrust/5' }}">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md bg-cloud text-tealTrust"><x-heroicon-o-building-office-2 class="h-5 w-5" /></span>
                        <span class="font-semibold">{{ __('site.nav.hospitals') }}</span>
                    </a>
                    <a href="{{ route('hotels.index') }}" @click="mobileNavOpen = false" class="group flex min-h-14 items-center gap-3 rounded-md border px-3 py-2.5 transition {{ request()->routeIs('hotels.*') ? 'border-tealTrust/30 bg-tealTrust/10 text-tealTrust' : 'border-slate-200 text-navyDeep hover:border-tealTrust/30 hover:bg-tealTrust/5' }}">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md bg-cloud text-tealTrust"><x-heroicon-o-building-storefront class="h-5 w-5" /></span>
                        <span class="font-semibold">{{ __('site.nav.hotel_booking') }}</span>
                    </a>
                    <a href="{{ route('treatments.index') }}" @click="mobileNavOpen = false" class="group flex min-h-14 items-center gap-3 rounded-md border px-3 py-2.5 transition {{ request()->routeIs('treatments.*') ? 'border-tealTrust/30 bg-tealTrust/10 text-tealTrust' : 'border-slate-200 text-navyDeep hover:border-tealTrust/30 hover:bg-tealTrust/5' }}">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md bg-cloud text-tealTrust"><x-heroicon-o-heart class="h-5 w-5" /></span>
                        <span class="font-semibold">{{ __('site.nav.treatments') }}</span>
                    </a>
                    <a href="{{ route('cost-estimator.index') }}" @click="mobileNavOpen = false" class="group flex min-h-14 items-center gap-3 rounded-md border px-3 py-2.5 transition {{ request()->routeIs('cost-estimator.*') ? 'border-tealTrust/30 bg-tealTrust/10 text-tealTrust' : 'border-slate-200 text-navyDeep hover:border-tealTrust/30 hover:bg-tealTrust/5' }}">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md bg-cloud text-tealTrust"><x-heroicon-o-banknotes class="h-5 w-5" /></span>
                        <span class="font-semibold">{{ __('site.nav.cost') }}</span>
                    </a>
                    <a href="{{ route('visa-support') }}" @click="mobileNavOpen = false" class="group flex min-h-14 items-center gap-3 rounded-md border px-3 py-2.5 transition {{ request()->routeIs('visa-support') ? 'border-tealTrust/30 bg-tealTrust/10 text-tealTrust' : 'border-slate-200 text-navyDeep hover:border-tealTrust/30 hover:bg-tealTrust/5' }}">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md bg-cloud text-tealTrust"><x-heroicon-o-document-check class="h-5 w-5" /></span>
                        <span class="font-semibold">{{ __('site.nav.visa') }}</span>
                    </a>
                    <a href="{{ route('track.index') }}" @click="mobileNavOpen = false" class="group flex min-h-14 items-center gap-3 rounded-md border px-3 py-2.5 transition {{ request()->routeIs('track.*') ? 'border-tealTrust/30 bg-tealTrust/10 text-tealTrust' : 'border-slate-200 text-navyDeep hover:border-tealTrust/30 hover:bg-tealTrust/5' }}">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md bg-cloud text-tealTrust"><x-heroicon-o-map class="h-5 w-5" /></span>
                        <span class="font-semibold">{{ __('site.nav.track') }}</span>
                    </a>
                </nav>

                <div class="mt-4 grid grid-cols-[1fr_auto] gap-2 border-t border-slate-200 pt-4">
                    <div class="inline-flex overflow-hidden rounded-md border border-slate-200 text-center text-sm font-semibold">
                        <a href="{{ route('language.switch', 'bn') }}" class="flex-1 px-3 py-2.5 {{ $currentLocale === 'bn' ? 'bg-tealTrust text-white' : 'bg-white text-slate-600' }}">বাংলা</a>
                        <a href="{{ route('language.switch', 'en') }}" class="flex-1 px-3 py-2.5 {{ $currentLocale === 'en' ? 'bg-tealTrust text-white' : 'bg-white text-slate-600' }}">English</a>
                    </div>
                    <a href="https://wa.me/{{ $whatsapp }}" class="btn-primary px-4 py-2.5" target="_blank" rel="noopener noreferrer" aria-label="{{ __('site.nav.whatsapp') }}">
                        <x-heroicon-o-chat-bubble-left-right class="h-5 w-5" />
                        <span class="hidden sm:inline">{{ __('site.nav.whatsapp') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main>
        @if (session('ref_number'))
            <div class="bg-successCare text-white">
                <div class="container-page py-3 text-sm font-semibold">{{ __('site.ref_number', ['ref' => session('ref_number')]) }}</div>
            </div>
        @endif
        {{ $slot }}
    </main>

    <footer class="border-t border-tealTrust/15 bg-[#eef5f4] pb-20 pt-12 xl:pb-0">
        <div class="container-page grid gap-10 pb-10 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <img
                    src="{{ asset('images/asian-health-connect-logo.png') }}"
                    alt="Asian Health Connect"
                    class="mb-5 h-14 w-auto max-w-[260px] object-contain"
                    width="2172"
                    height="724"
                    loading="lazy"
                >
                <p class="readable-copy max-w-xl text-sm leading-7 text-slate-600">{{ __('site.footer_text') }}</p>
                @if ($footerSocials->isNotEmpty())
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach ($footerSocials as $social)
                            @php
                                $socialIcon = match ($social->platform) {
                                    'youtube' => 'heroicon-o-play-circle',
                                    'instagram' => 'heroicon-o-camera',
                                    'linkedin' => 'heroicon-o-briefcase',
                                    default => 'heroicon-o-user-group',
                                };
                            @endphp
                            <a
                                href="{{ $social->url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-md border border-tealTrust/15 bg-white px-3 py-2 text-sm font-semibold text-navyDeep transition hover:border-tealTrust hover:text-tealTrust"
                            >
                                <x-dynamic-component :component="$socialIcon" class="h-4 w-4 text-tealTrust" />
                                <span>{{ $social->name }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="sm:grid sm:grid-cols-2 sm:gap-8 lg:col-span-7 lg:grid-cols-[.7fr_1.3fr]">
                <div>
                    <div class="mb-4 text-sm font-bold uppercase text-navyDeep">{{ __('site.nav.explore') }}</div>
                    <div class="grid gap-3 text-sm text-slate-600">
                        <a href="{{ route('hotels.index') }}" class="transition hover:text-tealTrust">{{ __('site.nav.hotel_booking') }}</a>
                        <a href="{{ route('services.index') }}" class="transition hover:text-tealTrust">{{ __('site.nav.services') }}</a>
                        <a href="{{ route('reviews.index') }}" class="transition hover:text-tealTrust">{{ __('site.nav.reviews') }}</a>
                        <a href="{{ route('blog.index') }}" class="transition hover:text-tealTrust">{{ __('site.nav.blog') }}</a>
                        <a href="{{ route('faq') }}" class="transition hover:text-tealTrust">{{ __('site.nav.faq') }}</a>
                    </div>
                </div>
                <div class="mt-9 sm:mt-0">
                    <div class="mb-4 text-sm font-bold uppercase text-navyDeep">{{ __('site.nav.contact') }}</div>
                    <div class="grid gap-4 text-sm">
                        @forelse ($footerPhones as $channel)
                            <a href="{{ $channel->destinationUrl() }}" class="group flex items-start gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md bg-white text-tealTrust shadow-sm"><x-heroicon-o-phone class="h-4 w-4" /></span>
                                <span><strong class="block text-navyDeep group-hover:text-tealTrust">{{ $channel->label }}</strong><span class="text-slate-600">{{ $channel->value }}</span></span>
                            </a>
                        @empty
                            @if ($siteSettings['support_phone'] ?? null)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $siteSettings['support_phone']) }}" class="flex items-center gap-3 text-slate-600"><x-heroicon-o-phone class="h-5 w-5 text-tealTrust" />{{ $siteSettings['support_phone'] }}</a>
                            @endif
                        @endforelse

                        @foreach ($footerWhatsapps as $channel)
                            <a href="{{ $channel->destinationUrl() }}" target="_blank" rel="noopener noreferrer" class="group flex items-start gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md bg-white text-tealTrust shadow-sm"><x-heroicon-o-chat-bubble-left-right class="h-4 w-4" /></span>
                                <span><strong class="block text-navyDeep group-hover:text-tealTrust">{{ $channel->label }}</strong><span class="text-slate-600">{{ $channel->value }}</span></span>
                            </a>
                        @endforeach

                        @forelse ($footerEmails as $channel)
                            <a href="{{ $channel->destinationUrl() }}" class="group flex items-start gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md bg-white text-tealTrust shadow-sm"><x-heroicon-o-envelope class="h-4 w-4" /></span>
                                <span class="min-w-0"><strong class="block text-navyDeep group-hover:text-tealTrust">{{ $channel->label }}</strong><span class="break-words text-slate-600">{{ $channel->value }}</span></span>
                            </a>
                        @empty
                            @if ($siteSettings['support_email'] ?? null)
                                <a href="mailto:{{ $siteSettings['support_email'] }}" class="flex items-center gap-3 break-words text-slate-600"><x-heroicon-o-envelope class="h-5 w-5 shrink-0 text-tealTrust" />{{ $siteSettings['support_email'] }}</a>
                            @endif
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        @if ($footerOffices->isNotEmpty())
            <div class="border-t border-tealTrust/15 bg-white/55">
                <div class="container-page py-9">
                    <div class="mb-6 flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-md bg-tealTrust text-white"><x-heroicon-o-map-pin class="h-5 w-5" /></span>
                        <div>
                            <h2 class="font-bold text-navyDeep">{{ __('site.footer.offices') }}</h2>
                            <p class="text-sm text-slate-600">{{ __('site.footer.offices_sub') }}</p>
                        </div>
                    </div>
                    <div class="grid gap-x-8 gap-y-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($footerOffices as $office)
                            <address class="border-l-2 border-accent/70 pl-4 not-italic">
                                @if ($office->map_url)
                                    <a href="{{ $office->map_url }}" target="_blank" rel="noopener noreferrer" class="font-bold text-navyDeep transition hover:text-tealTrust">{{ $office->name }}</a>
                                @else
                                    <div class="font-bold text-navyDeep">{{ $office->name }}</div>
                                @endif
                                <div class="mt-1 text-xs font-semibold uppercase text-tealTrust">{{ $office->district }}</div>
                                <p class="readable-copy mt-2 text-sm leading-6 text-slate-600">{{ $office->address }}</p>
                                @if ($office->phone)
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $office->phone) }}" class="mt-2 inline-flex items-center gap-2 text-sm text-slate-600 hover:text-tealTrust"><x-heroicon-o-phone class="h-4 w-4" />{{ $office->phone }}</a>
                                @endif
                            </address>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-navyDeep py-4 text-white/70">
            <div class="container-page flex flex-col gap-2 text-xs sm:flex-row sm:items-center sm:justify-between">
                <span>&copy; {{ date('Y') }} Asian Health Connect. {{ __('site.footer.rights') }}</span>
                <span>{{ __('site.footer.care_line') }}</span>
            </div>
        </div>
    </footer>

    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 shadow-[0_-8px_24px_rgba(15,47,70,0.08)] backdrop-blur xl:hidden" aria-label="{{ __('site.nav.mobile_shortcuts') }}">
        <div class="grid grid-cols-5 text-center text-[10px] font-semibold text-slate-600 sm:text-xs">
            <a class="flex min-h-16 flex-col items-center justify-center gap-1 py-2 {{ request()->routeIs('home') ? 'text-tealTrust' : '' }}" href="{{ route('home') }}">
                <x-heroicon-o-home class="h-5 w-5" />
                <span>{{ __('site.nav.home') }}</span>
            </a>
            <a class="flex min-h-16 flex-col items-center justify-center gap-1 py-2 {{ request()->routeIs('doctors.*') ? 'text-tealTrust' : '' }}" href="{{ route('doctors.index') }}">
                <x-heroicon-o-user-group class="h-5 w-5" />
                <span>{{ __('site.nav.doctors') }}</span>
            </a>
            <a class="flex min-h-16 flex-col items-center justify-center gap-1 py-2 {{ request()->routeIs('hospitals.*') ? 'text-tealTrust' : '' }}" href="{{ route('hospitals.index') }}">
                <x-heroicon-o-building-office-2 class="h-5 w-5" />
                <span>{{ __('site.nav.hospitals') }}</span>
            </a>
            <button type="button" class="flex min-h-16 flex-col items-center justify-center gap-1 py-2" @click="mobileNavOpen = ! mobileNavOpen" :class="mobileNavOpen ? 'text-tealTrust' : ''" :aria-expanded="mobileNavOpen.toString()" aria-controls="mobile-navigation">
                <x-heroicon-o-squares-2x2 class="h-5 w-5" />
                <span>{{ __('site.nav.menu') }}</span>
            </button>
            <a class="flex min-h-16 flex-col items-center justify-center gap-1 py-2 text-tealTrust" href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener noreferrer">
                <x-heroicon-o-chat-bubble-left-right class="h-5 w-5" />
                <span>{{ __('site.nav.whatsapp') }}</span>
            </a>
        </div>
    </nav>
</body>
</html>
