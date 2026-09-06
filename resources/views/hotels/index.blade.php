<x-layouts.app :title="__('site.hotels.title') . ' - Asian Health Connect'">
    <section class="border-b border-slate-200 bg-slate-50">
        <div class="container-page py-10 md:py-14">
            <div class="max-w-3xl">
                <p class="text-sm font-bold text-tealTrust">{{ __('site.nav.hotel_booking') }}</p>
                <h1 class="mt-2 text-3xl font-bold text-navyDeep md:text-4xl">{{ __('site.hotels.title') }}</h1>
                <p class="mt-3 leading-7 text-slate-600">{{ __('site.hotels.subtitle') }}</p>
            </div>

            <form
                action="{{ route('hotels.index') }}"
                method="GET"
                class="mt-8 grid gap-4 border border-slate-200 bg-white p-5 shadow-sm lg:grid-cols-4"
                x-data="{
                    country: @js((string) request('country', '')),
                    city: @js((string) request('city', '')),
                    hospital: @js((string) request('hospital', '')),
                    cities: @js($filterCities),
                    hospitals: @js($filterHospitals),
                    cityOptions() { return this.cities.filter(item => String(item.country_id) === this.country) },
                    hospitalOptions() { return this.hospitals.filter(item => String(item.country_id) === this.country && String(item.city_id) === this.city) },
                    changeCountry() { this.city = ''; this.hospital = '' },
                    changeCity() { this.hospital = '' }
                }"
            >
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    {{ __('site.hotels.country') }}
                    <select name="country" x-model="country" @change="changeCountry" class="rounded-md border-slate-200">
                        <option value="">{{ __('site.hotels.all_countries') }}</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    {{ __('site.hotels.city') }}
                    <select name="city" x-model="city" @change="changeCity" :disabled="!country" class="rounded-md border-slate-200 disabled:bg-slate-100">
                        <option value="" x-text="country ? @js(__('site.common.all_cities')) : @js(__('site.hotels.select_country_first'))"></option>
                        <template x-for="item in cityOptions()" :key="item.id">
                            <option :value="String(item.id)" x-text="item.name"></option>
                        </template>
                    </select>
                </label>

                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    {{ __('site.hotels.hospital') }}
                    <select name="hospital" x-model="hospital" :disabled="!city" class="rounded-md border-slate-200 disabled:bg-slate-100">
                        <option value="" x-text="city ? @js(__('site.hotels.all_hospitals')) : @js(__('site.hotels.select_city_first'))"></option>
                        <template x-for="item in hospitalOptions()" :key="item.id">
                            <option :value="String(item.id)" x-text="item.name"></option>
                        </template>
                    </select>
                </label>

                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    {{ __('site.hotels.hotel_name') }}
                    <input name="q" value="{{ request('q') }}" maxlength="100" placeholder="{{ __('site.hotels.search_hotel') }}" class="rounded-md border-slate-200">
                </label>

                <div class="flex flex-wrap gap-3 lg:col-span-4">
                    <button class="btn-primary">{{ __('site.hotels.filter') }}</button>
                    <a href="{{ route('hotels.index') }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">{{ __('site.hotels.reset') }}</a>
                </div>
            </form>
        </div>
    </section>

    <section class="container-page py-10 md:py-14">
        @if ($hotels->isEmpty())
            <div class="border border-slate-200 bg-white p-8 text-center shadow-sm">
                <h2 class="text-xl font-bold text-navyDeep">{{ __('site.hotels.no_hotels') }}</h2>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($hotels as $hotel)
                    @php($images = $hotel->imageUrls())
                    <article class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                        @if ($images !== [])
                            <img src="{{ $images[0] }}" alt="{{ $hotel->name }}" class="aspect-[16/10] w-full object-cover" loading="lazy">
                        @else
                            <div class="flex aspect-[16/10] items-center justify-center bg-slate-100 text-sm font-semibold text-slate-500">{{ $hotel->name }}</div>
                        @endif

                        <div class="p-5">
                            <p class="text-sm font-semibold text-tealTrust">{{ $hotel->city?->name }}, {{ $hotel->country?->name }}</p>
                            <h2 class="mt-2 text-xl font-bold text-navyDeep">{{ $hotel->name }}</h2>
                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ $hotel->address }}</p>

                            @if ($hotel->estimatedCostLabel())
                                <div class="mt-4 border-l-4 border-orangeAction bg-orange-50 px-4 py-3">
                                    <p class="text-xs font-bold uppercase text-slate-500">{{ __('site.hotels.estimated') }}</p>
                                    <p class="mt-1 font-bold text-navyDeep">{{ $hotel->estimatedCostLabel() }}</p>
                                </div>
                            @endif

                            @if ($hotel->hospitals->isNotEmpty())
                                <p class="mt-4 text-sm leading-6 text-slate-600">
                                    <span class="font-bold text-navyDeep">{{ __('site.hotels.nearby_hospitals') }}:</span>
                                    {{ $hotel->hospitals->pluck('name')->join(', ') }}
                                </p>
                            @endif

                            <div class="mt-5 flex flex-wrap gap-3">
                                <a href="{{ route('hotels.show', $hotel) }}" class="inline-flex items-center justify-center rounded-md border border-tealTrust px-4 py-2 text-sm font-bold text-tealTrust hover:bg-tealTrust hover:text-white">{{ __('site.hotels.view_details') }}</a>
                                <a href="{{ route('hotels.show', $hotel) }}#booking" class="btn-primary px-4 py-2">{{ __('site.hotels.request_booking') }}</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">{{ $hotels->links() }}</div>
        @endif
    </section>
</x-layouts.app>
