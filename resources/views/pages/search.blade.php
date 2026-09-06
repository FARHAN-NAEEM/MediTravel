<x-layouts.app :title="__('site.search.title') . ' - Asian Health Connect'">
    <section class="container-page py-10 md:py-14">
        <div class="max-w-3xl">
            <p class="text-sm font-bold text-tealTrust">{{ __('site.search.kicker') }}</p>
            <h1 class="mt-2 text-3xl font-bold text-navyDeep md:text-4xl">{{ __('site.search.title') }}</h1>
            <p class="mt-3 text-slate-600">{{ __('site.search.summary', ['query' => $query]) }}</p>
        </div>

        <form action="{{ route('search') }}" method="GET" class="mt-7 grid max-w-3xl gap-3 border border-slate-200 bg-white p-3 shadow-sm sm:grid-cols-[1fr_auto]">
            <input name="q" value="{{ $query }}" required minlength="2" maxlength="100" class="rounded-md border-slate-200" aria-label="{{ __('site.home.search_placeholder') }}">
            <button class="btn-primary">{{ __('site.home.search') }}</button>
        </form>

        @if ($hospitals->isEmpty() && $hotels->isEmpty() && $doctors->isEmpty() && $treatments->isEmpty())
            <div class="mt-10 border border-slate-200 bg-white p-8 text-center shadow-sm">
                <h2 class="text-xl font-bold text-navyDeep">{{ __('site.search.no_results') }}</h2>
                <p class="mt-2 text-slate-600">{{ __('site.search.no_results_help') }}</p>
            </div>
        @endif

        @if ($hotels->isNotEmpty())
            <div class="mt-12">
                <div class="flex items-end justify-between gap-4 border-b border-slate-200 pb-4">
                    <h2 class="text-2xl font-bold text-navyDeep">{{ __('site.search.hotels') }}</h2>
                    <span class="text-sm font-semibold text-slate-500">{{ $hotels->count() }} {{ __('site.search.results') }}</span>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    @foreach ($hotels as $hotel)
                        <a href="{{ route('hotels.show', $hotel) }}" class="border border-slate-200 bg-white p-5 shadow-sm transition hover:border-tealTrust/40 hover:shadow-md">
                            <h3 class="text-lg font-bold text-navyDeep">{{ $hotel->name }}</h3>
                            <p class="mt-1 text-sm text-slate-600">{{ $hotel->city?->name }}, {{ $hotel->country?->name }}</p>
                            <p class="mt-3 line-clamp-2 text-sm text-slate-600">{{ $hotel->hospitals->pluck('name')->join(', ') }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($hospitals->isNotEmpty())
            <div class="mt-12">
                <div class="flex items-end justify-between gap-4 border-b border-slate-200 pb-4">
                    <h2 class="text-2xl font-bold text-navyDeep">{{ __('site.search.hospitals') }}</h2>
                    <span class="text-sm font-semibold text-slate-500">{{ $hospitals->count() }} {{ __('site.search.results') }}</span>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    @foreach ($hospitals as $hospital)
                        <a href="{{ route('hospitals.show', $hospital) }}" class="border border-slate-200 bg-white p-5 shadow-sm transition hover:border-tealTrust/40 hover:shadow-md">
                            <h3 class="text-lg font-bold text-navyDeep">{{ $hospital->name }}</h3>
                            <p class="mt-1 text-sm text-slate-600">{{ $hospital->city?->name }}, {{ $hospital->country?->name }}</p>
                            @if ($hospital->accreditation)
                                <p class="mt-3 text-sm font-semibold text-tealTrust">{{ $hospital->accreditation }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($treatments->isNotEmpty())
            <div class="mt-12">
                <div class="flex items-end justify-between gap-4 border-b border-slate-200 pb-4">
                    <h2 class="text-2xl font-bold text-navyDeep">{{ __('site.search.treatments') }}</h2>
                    <span class="text-sm font-semibold text-slate-500">{{ $treatments->count() }} {{ __('site.search.results') }}</span>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    @foreach ($treatments as $treatment)
                        <a href="{{ route('treatments.show', $treatment) }}" class="border border-slate-200 bg-white p-5 shadow-sm transition hover:border-tealTrust/40 hover:shadow-md">
                            <h3 class="text-lg font-bold text-navyDeep">{{ $treatment->name }}</h3>
                            <p class="mt-1 text-sm text-slate-600">{{ $treatment->department?->name }}</p>
                            <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">{{ $treatment->description }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($doctors->isNotEmpty())
            <div class="mt-12">
                <div class="flex items-end justify-between gap-4 border-b border-slate-200 pb-4">
                    <h2 class="text-2xl font-bold text-navyDeep">{{ __('site.search.doctors') }}</h2>
                    <span class="text-sm font-semibold text-slate-500">{{ $doctors->count() }} {{ __('site.search.results') }}</span>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    @foreach ($doctors as $doctor)
                        <a href="{{ route('doctors.show', $doctor) }}" class="border border-slate-200 bg-white p-5 shadow-sm transition hover:border-tealTrust/40 hover:shadow-md">
                            <h3 class="text-lg font-bold text-navyDeep">{{ $doctor->name }}</h3>
                            <p class="mt-1 text-sm text-slate-600">{{ $doctor->designation }}</p>
                            <p class="mt-3 text-sm font-semibold text-tealTrust">{{ $doctor->hospital?->name }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
</x-layouts.app>
