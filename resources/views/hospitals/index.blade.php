<x-layouts.app title="{{ __('hospitals.title') }} - Asian Health Connect">
    <section class="hospital-directory">
        <div class="container-page">
            <nav class="mb-5 text-sm text-slate-500" aria-label="Breadcrumb"><a href="{{ route('home') }}">{{ __('site.nav.home') }}</a><span aria-hidden="true" class="mx-2">/</span>{{ __('site.nav.hospitals') }}</nav>
            <h1 class="section-title">{{ __('hospitals.title') }}</h1>
            <p class="mt-3 max-w-3xl leading-7 text-slate-600">{{ __('hospitals.intro') }}</p>
            <form action="{{ route('hospitals.index') }}#hospital-results" method="GET" class="hospital-filters" x-data="{ country: @js($filters['country'] ?? ''), city: @js($filters['city'] ?? '') }">
                <div class="hospital-filters__search">
                    <label for="hospital-search">{{ __('hospitals.search') }}</label>
                    <input id="hospital-search" name="q" value="{{ $search }}" maxlength="120" placeholder="{{ __('hospitals.search_placeholder') }}" type="search">
                </div>
                <div>
                    <label for="hospital-country">{{ __('hospitals.country') }}</label>
                    <select id="hospital-country" name="country" x-model="country" @change="city = ''">
                        <option value="">{{ __('hospitals.all_countries') }}</option>
                        @foreach ($countries as $country)<option value="{{ $country->slug }}" @selected(($filters['country'] ?? '') === $country->slug)>{{ trans('hospitals.locations')[$country->name] ?? $country->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="hospital-city">{{ __('hospitals.city') }}</label>
                    <select id="hospital-city" name="city" x-model="city">
                        <option value="">{{ __('hospitals.all_cities') }}</option>
                        @foreach ($cities as $city)<option value="{{ $city->slug }}" :disabled="country !== '' && country !== @js($city->country->slug)" @selected(($filters['city'] ?? '') === $city->slug)>{{ trans('hospitals.locations')[$city->name] ?? $city->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="hospital-group">{{ __('hospitals.group') }}</label>
                    <select id="hospital-group" name="group">
                        <option value="">{{ __('hospitals.all_groups') }}</option>
                        @foreach ($groups as $group)<option value="{{ $group->slug }}" @selected(($filters['group'] ?? '') === $group->slug)>{{ $group->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="hospital-care">{{ __('hospitals.care') }}</label>
                    <select id="hospital-care" name="care">
                        <option value="">{{ __('hospitals.all_care') }}</option>
                        @foreach (\App\Models\Hospital::CARE_TYPES as $care)<option value="{{ $care }}" @selected(($filters['care'] ?? '') === $care)>{{ __('hospitals.care_types.'.$care) }}</option>@endforeach
                    </select>
                </div>
                <button class="btn-primary gap-2"><x-heroicon-o-magnifying-glass class="h-5 w-5 shrink-0" />{{ __('hospitals.filter') }}</button>
            </form>
            @if ($groups->isNotEmpty())
                <div class="hospital-brands hospital-brands--primary" aria-label="{{ __('hospitals.browse_group') }}">
                    <a href="{{ route('hospitals.index', array_filter(\Illuminate\Support\Arr::except($filters, ['group']))) }}" class="hospital-brand {{ empty($filters['group']) ? 'hospital-brand--active' : '' }}" @if(empty($filters['group'])) aria-current="true" @endif><x-heroicon-o-building-office-2 class="h-8 w-8 text-tealTrust" /><span>{{ __('hospitals.all') }}</span></a>
                    @foreach ($groups->take(7) as $group)@include('hospitals.partials.brand')@endforeach
                </div>
                @if ($groups->count() > 7)
                    <details class="hospital-brands-more" @if($groups->skip(7)->contains('slug', $filters['group'] ?? '')) open @endif>
                        <summary>{{ __('hospitals.more_groups') }} ({{ $groups->count() - 7 }})</summary>
                        <div class="hospital-brands">@foreach ($groups->skip(7) as $group)@include('hospitals.partials.brand')@endforeach</div>
                    </details>
                @endif
            @endif
        </div>
    </section>
    <section class="container-page py-8" id="hospital-results" aria-label="{{ __('site.nav.hospitals') }}">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-semibold">{{ __('hospitals.results', ['count' => $hospitals->total()]) }}</h2>
            @if (count(array_filter($filters)))<a class="text-sm font-semibold text-tealTrust underline underline-offset-4" href="{{ route('hospitals.index') }}">{{ __('hospitals.clear') }}</a>@endif
        </div>
        <div class="hospital-grid">
            @forelse ($hospitals as $hospital)<x-hospital-card :hospital="$hospital" />
            @empty<div class="col-span-full py-12 text-center"><x-heroicon-o-magnifying-glass class="mx-auto mb-4 h-8 w-8 text-slate-400" /><h3 class="font-bold">{{ __('hospitals.empty') }}</h3><p class="mt-2 text-slate-600">{{ __('hospitals.empty_help') }}</p></div>@endforelse
        </div>
        <div class="mt-8">{{ $hospitals->links() }}</div>
    </section>
    <section class="hospital-support">
        <div class="container-page flex flex-col justify-between gap-5 md:flex-row md:items-center">
            <div class="max-w-2xl"><h2 class="text-xl font-bold">{{ __('hospitals.support_title') }}</h2><p class="mt-2 leading-7 text-slate-600">{{ __('hospitals.support_copy') }}</p></div>
            <a href="{{ route('contact') }}" class="btn-primary shrink-0 gap-2"><x-heroicon-o-chat-bubble-left-right class="h-5 w-5" />{{ __('hospitals.talk') }}</a>
        </div>
    </section>
    <p class="container-page py-6 text-xs leading-6 text-slate-500">{{ __('hospitals.directory_note') }}</p>
</x-layouts.app>
