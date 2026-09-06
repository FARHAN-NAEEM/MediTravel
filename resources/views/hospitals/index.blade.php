<x-layouts.app :title="__('site.pages.hospital_directory') . ' - Asian Health Connect'">
    <section class="container-page py-10">
        <h1 class="section-title">{{ __('site.pages.hospital_directory') }}</h1>
        <form class="mt-6 grid gap-3 rounded-lg bg-white p-4 shadow-sm md:grid-cols-4">
            <input name="q" value="{{ request('q') }}" class="rounded-md border-slate-200 md:col-span-2" placeholder="{{ __('site.common.search_hospital') }}">
            <select name="city" class="rounded-md border-slate-200">
                <option value="">{{ __('site.common.all_cities') }}</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->slug }}" @selected(request('city') === $city->slug)>{{ $city->name }}, {{ $city->country->name }}</option>
                @endforeach
            </select>
            <button class="btn-primary">{{ __('site.common.filter') }}</button>
        </form>
        <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($hospitals as $hospital)
                @php($cardHighlight = $hospital->cardHighlight())
                <a
                    href="{{ route('hospitals.show', $hospital) }}"
                    class="group card flex h-full min-w-0 flex-col overflow-hidden transition duration-300 hover:-translate-y-1 hover:border-tealTrust/30 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-tealTrust focus:ring-offset-2"
                >
                    <div class="relative flex h-44 items-center justify-center overflow-hidden border-b border-tealTrust/10 bg-[#edf7f6]">
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
                        @if ($hospital->is_featured)
                            <span class="absolute right-3 top-3 rounded-md border border-white/80 bg-white/95 px-2.5 py-1 text-xs font-bold text-tealTrust shadow-sm">
                                {{ __('site.hospital_cards.featured') }}
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-1 flex-col p-5">
                        <h2 class="readable-heading text-lg font-bold leading-7 text-navyDeep transition group-hover:text-tealTrust">
                            {{ $hospital->name }}
                        </h2>

                        <div class="mt-2 flex items-start gap-2 text-sm text-slate-600">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-tealTrust" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s6-5.3 6-11a6 6 0 1 0-12 0c0 5.7 6 11 6 11Z"/>
                                <circle cx="12" cy="10" r="2.2"/>
                            </svg>
                            <span>{{ $hospital->city->name }}, {{ $hospital->city->country->name }}</span>
                        </div>

                        @if (filled($hospital->accreditation))
                            <div class="mt-3 flex items-start gap-2 text-sm font-medium text-tealTrust">
                                <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5.5 5.7v5.8c0 4.2 2.7 7.9 6.5 9.5 3.8-1.6 6.5-5.3 6.5-9.5V5.7L12 3Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/>
                                </svg>
                                <span>{{ $hospital->accreditation }}</span>
                            </div>
                        @endif

                        @if ($cardHighlight)
                            <div class="mt-5 flex items-start gap-2.5 border-l-2 border-accent bg-slate-50 px-3 py-2.5 text-sm leading-6 text-slate-700">
                                <svg class="mt-1 h-4 w-4 shrink-0 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21C8.5 17.5 4 14.4 4 9.5A4.5 4.5 0 0 1 12 6.7a4.5 4.5 0 0 1 8 2.8c0 4.9-4.5 8-8 11.5Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 12h2l1.2-2.5 2.2 5 1.1-2.5h2.5"/>
                                </svg>
                                <span class="text-wrap-anywhere">{{ $cardHighlight }}</span>
                            </div>
                        @endif
                    </div>
                </a>
            @empty
                <div class="card px-6 py-10 text-center text-slate-600 sm:col-span-2 xl:col-span-3">
                    {{ __('site.hospital_cards.no_results') }}
                </div>
            @endforelse
        </div>
        <div class="mt-8">{{ $hospitals->links() }}</div>
    </section>
</x-layouts.app>
