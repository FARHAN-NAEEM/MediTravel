<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $description ?? __('site.meta_description') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @php
        $whatsapp = $siteSettings['whatsapp_number'] ?? config('services.whatsapp.number', env('WHATSAPP_NUMBER', '8801700000000'));
        $currentLocale = app()->getLocale();
    @endphp

    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="container-page flex h-16 items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-navyDeep">
                <span class="grid h-9 w-9 place-items-center rounded-md bg-tealTrust text-white">M</span>
                <span>MediTravel</span>
            </a>
            <nav class="hidden items-center gap-6 text-sm font-semibold text-slate-700 lg:flex">
                <a href="{{ route('doctors.index') }}" class="hover:text-tealTrust">{{ __('site.nav.doctors') }}</a>
                <a href="{{ route('hospitals.index') }}" class="hover:text-tealTrust">{{ __('site.nav.hospitals') }}</a>
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
                <a href="https://wa.me/{{ $whatsapp }}" class="btn-primary py-2">{{ __('site.nav.whatsapp') }}</a>
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

    <footer class="border-t border-slate-200 bg-white pb-20 pt-10 lg:pb-10">
        <div class="container-page grid gap-8 md:grid-cols-4">
            <div class="md:col-span-2">
                <div class="mb-3 text-lg font-bold">MediTravel</div>
                <p class="max-w-xl text-sm leading-7 text-slate-600">{{ __('site.footer_text') }}</p>
            </div>
            <div>
                <div class="mb-3 font-semibold">{{ __('site.nav.explore') }}</div>
                <div class="grid gap-2 text-sm text-slate-600">
                    <a href="{{ route('services.index') }}">{{ __('site.nav.services') }}</a>
                    <a href="{{ route('reviews.index') }}">{{ __('site.nav.reviews') }}</a>
                    <a href="{{ route('blog.index') }}">{{ __('site.nav.blog') }}</a>
                    <a href="{{ route('faq') }}">{{ __('site.nav.faq') }}</a>
                </div>
            </div>
            <div>
                <div class="mb-3 font-semibold">{{ __('site.nav.contact') }}</div>
                <div class="grid gap-2 text-sm text-slate-600">
                    <span>{{ $siteSettings['support_phone'] ?? env('SUPPORT_PHONE') }}</span>
                    <span>{{ $siteSettings['support_email'] ?? env('SUPPORT_EMAIL') }}</span>
                </div>
            </div>
        </div>
    </footer>

    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white lg:hidden">
        <div class="grid grid-cols-4 text-center text-xs font-semibold text-slate-700">
            <a class="py-3" href="{{ route('home') }}">{{ __('site.nav.home') }}</a>
            <a class="py-3" href="{{ route('doctors.index') }}">{{ __('site.nav.doctors') }}</a>
            <a class="py-3" href="{{ route('hospitals.index') }}">{{ __('site.nav.hospitals') }}</a>
            <a class="py-3 text-tealTrust" href="https://wa.me/{{ $whatsapp }}">{{ __('site.nav.whatsapp') }}</a>
        </div>
    </nav>
</body>
</html>
