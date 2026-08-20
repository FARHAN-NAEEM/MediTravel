<x-layouts.app :title="__('site.home.title')">
    <section class="bg-white">
        <div class="container-page grid min-h-[620px] items-center gap-10 py-12 lg:grid-cols-[1.05fr_.95fr]">
            <div>
                <div class="mb-4 inline-flex rounded-md bg-tealTrust/10 px-3 py-2 text-sm font-semibold text-tealTrust">{{ __('site.home.eyebrow') }}</div>
                <h1 class="max-w-3xl text-4xl font-extrabold leading-tight tracking-normal text-navyDeep md:text-6xl">{{ __('site.home.headline') }}</h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">{{ __('site.home.subhead') }}</p>
                <form action="{{ route('doctors.index') }}" class="mt-8 grid gap-3 rounded-lg border border-slate-200 bg-cloud p-3 shadow-sm sm:grid-cols-[1fr_auto]">
                    <input name="q" class="rounded-md border-slate-200" placeholder="{{ __('site.home.search_placeholder') }}">
                    <button class="btn-primary">{{ __('site.home.search') }}</button>
                </form>
                <div class="mt-8 grid grid-cols-3 gap-4">
                    <div><div class="text-3xl font-bold text-tealTrust">{{ $stats['hospitals'] }}+</div><div class="text-sm text-slate-500">{{ __('site.home.hospitals') }}</div></div>
                    <div><div class="text-3xl font-bold text-tealTrust">{{ $stats['doctors'] }}+</div><div class="text-sm text-slate-500">{{ __('site.home.doctors') }}</div></div>
                    <div><div class="text-3xl font-bold text-tealTrust">{{ $stats['patients'] }}+</div><div class="text-sm text-slate-500">{{ __('site.home.patients_guided') }}</div></div>
                </div>
            </div>
            <div
                x-data="{ active: 0, images: @js($heroImages->map(fn ($image) => ['url' => $image->imageUrl(), 'alt' => $image->alt_text ?: $image->title ?: 'Hospital image'])->values()) }"
                x-init="if (images.length > 1) setInterval(() => active = (active + 1) % images.length, 4500)"
                class="relative aspect-[4/3] overflow-hidden rounded-lg shadow-2xl"
            >
                @if ($heroImages->isNotEmpty())
                    <template x-for="(image, index) in images" :key="image.url">
                        <img
                            x-show="active === index"
                            x-transition.opacity.duration.700ms
                            class="absolute inset-0 h-full w-full object-cover"
                            :src="image.url"
                            :alt="image.alt"
                        >
                    </template>
                    @if ($heroImages->count() > 1)
                        <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-2">
                            @foreach ($heroImages as $index => $image)
                                <button
                                    type="button"
                                    class="h-2.5 w-2.5 rounded-full border border-white/70"
                                    :class="active === {{ $index }} ? 'bg-white' : 'bg-white/40'"
                                    @click="active = {{ $index }}"
                                    aria-label="Show hero image {{ $index + 1 }}"
                                ></button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <img class="h-full w-full object-cover" src="https://images.unsplash.com/photo-1586773860418-d37222d8fce3?auto=format&fit=crop&w=1200&q=80" alt="Hospital care team">
                @endif
            </div>
        </div>
    </section>

    <section class="py-14">
        <div class="container-page">
            <div class="mb-8 flex items-end justify-between gap-4">
                <div>
                    <h2 class="section-title">{{ __('site.home.featured_hospitals') }}</h2>
                    <p class="mt-2 text-slate-600">{{ __('site.home.featured_hospitals_sub') }}</p>
                </div>
                <a href="{{ route('hospitals.index') }}" class="btn-secondary hidden sm:inline-flex">{{ __('site.home.view_all') }}</a>
            </div>
            <div class="grid gap-5 md:grid-cols-3">
                @foreach ($featuredHospitals as $hospital)
                    <a href="{{ route('hospitals.show', $hospital) }}" class="card p-5 transition hover:-translate-y-1 hover:shadow-md">
                        <div class="mb-4 h-36 rounded-md bg-gradient-to-br from-tealTrust/20 to-accent/20"></div>
                        <div class="text-lg font-bold">{{ $hospital->name }}</div>
                        <div class="mt-1 text-sm text-slate-500">{{ $hospital->city->name }}, {{ $hospital->country->name }}</div>
                        <div class="mt-3 inline-flex rounded-md bg-tealTrust/10 px-2 py-1 text-xs font-semibold text-tealTrust">{{ $hospital->accreditation }}</div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white py-14">
        <div class="container-page">
            <h2 class="section-title">{{ __('site.home.how_it_works') }}</h2>
            <div class="mt-8 grid gap-5 md:grid-cols-4">
                @foreach (__('site.home.steps') as $step)
                    <div class="card p-5">
                        <div class="mb-4 grid h-10 w-10 place-items-center rounded-md bg-accent text-lg font-bold text-white">{{ $loop->iteration }}</div>
                        <div class="font-bold">{{ $step }}</div>
                    </div>
                @endforeach
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
