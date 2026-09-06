<x-layouts.app :title="__('site.pages.track_title') . ' - Asian Health Connect'">
    @php
        $statusIcons = [
            'new' => 'heroicon-o-sparkles',
            'contacted' => 'heroicon-o-phone-arrow-up-right',
            'documents_pending' => 'heroicon-o-document-text',
            'hospital_sent' => 'heroicon-o-building-office-2',
            'appointment_confirmed' => 'heroicon-o-calendar-days',
            'closed' => 'heroicon-o-check-badge',
        ];
    @endphp

    <section class="min-h-[70vh] border-b border-slate-200 bg-[#f4f8fa] py-12 md:py-20">
        <div class="container-page">
            <div class="mx-auto max-w-3xl">
                <header class="text-center">
                    <span class="mx-auto grid h-16 w-16 place-items-center rounded-lg border border-tealTrust/20 bg-white text-tealTrust shadow-[0_12px_30px_rgba(15,42,64,0.08)]">
                        <x-heroicon-o-document-magnifying-glass class="h-8 w-8" />
                    </span>
                    <p class="mt-6 text-sm font-bold text-tealTrust">{{ __('site.pages.track_kicker') }}</p>
                    <h1 class="mt-3 text-3xl font-bold leading-tight text-navyDeep md:text-5xl">{{ __('site.pages.track_title') }}</h1>
                    <p class="mx-auto mt-4 max-w-xl text-base leading-8 text-slate-600 md:text-lg">{{ __('site.pages.track_sub') }}</p>
                </header>

                <form action="{{ route('track.show') }}" method="POST" class="mt-10 rounded-lg border border-slate-200 bg-white p-5 shadow-[0_16px_40px_rgba(15,42,64,0.08)] md:p-7">
                    @csrf
                    <label for="ref_number" class="text-sm font-bold text-navyDeep">{{ __('site.pages.track_input_label') }}</label>
                    <div class="relative mt-3">
                        <x-heroicon-o-hashtag class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                        <input
                            id="ref_number"
                            name="ref_number"
                            value="{{ old('ref_number', $inquiry->ref_number ?? '') }}"
                            class="h-14 w-full rounded-md border-slate-200 pl-12 pr-4 text-base font-semibold uppercase text-navyDeep shadow-inner placeholder:font-normal placeholder:normal-case focus:border-tealTrust focus:ring-tealTrust"
                            placeholder="MT-260821-0001"
                            maxlength="40"
                            required
                        >
                    </div>
                    @error('ref_number')
                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-3 flex items-center gap-2 text-sm leading-6 text-slate-500">
                        <x-heroicon-o-shield-check class="h-5 w-5 shrink-0 text-tealTrust" />
                        <span>{{ __('site.pages.track_hint') }}</span>
                    </p>
                    <button class="btn-primary mt-5 w-full gap-2 py-3.5">
                        <x-heroicon-o-magnifying-glass class="h-5 w-5" />
                        <span>{{ __('site.pages.track_button') }}</span>
                    </button>
                </form>

                @isset($searched)
                    @if ($inquiry)
                        <article class="mt-8 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-[0_16px_40px_rgba(15,42,64,0.08)]">
                            <div class="border-b border-slate-200 bg-navyDeep p-6 text-white md:p-8">
                                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-4">
                                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-lg bg-white/10 text-white">
                                            <x-dynamic-component :component="$statusIcons[$inquiry->status] ?? 'heroicon-o-clock'" class="h-6 w-6" />
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-300">{{ __('site.common.current_status') }}</p>
                                            <h2 class="mt-1 text-2xl font-bold">{{ __('site.statuses.' . $inquiry->status) }}</h2>
                                        </div>
                                    </div>
                                    <div class="rounded-md border border-white/15 bg-white/5 px-4 py-3 sm:text-right">
                                        <p class="text-xs font-semibold text-slate-300">{{ __('site.common.reference') }}</p>
                                        <p class="mt-1 font-bold">{{ $inquiry->ref_number }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 md:p-8">
                                <div class="flex items-center gap-3">
                                    <x-heroicon-o-list-bullet class="h-6 w-6 text-tealTrust" />
                                    <h3 class="text-xl font-bold text-navyDeep">{{ __('site.pages.track_timeline_title') }}</h3>
                                </div>

                                <ol class="relative mt-7 ml-5 border-l-2 border-slate-200 pb-1">
                                    @forelse ($inquiry->logs->sortBy('created_at') as $log)
                                        <li class="relative pb-8 pl-9 last:pb-0">
                                            <span class="absolute -left-[21px] top-0 grid h-10 w-10 place-items-center rounded-full border-4 border-white bg-tealTrust text-white shadow-sm">
                                                <x-dynamic-component :component="$statusIcons[$log->status] ?? 'heroicon-o-clock'" class="h-5 w-5" />
                                            </span>
                                            <div class="{{ $loop->last ? '' : 'border-b border-slate-100 pb-7' }}">
                                                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                                    <h4 class="font-bold text-navyDeep">{{ __('site.statuses.' . $log->status) }}</h4>
                                                    <time class="text-xs font-semibold text-slate-400">{{ $log->created_at?->format('d M Y, h:i A') }}</time>
                                                </div>
                                                @if ($log->note)
                                                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $log->note }}</p>
                                                @endif
                                            </div>
                                        </li>
                                    @empty
                                        <li class="relative pl-9">
                                            <span class="absolute -left-[21px] top-0 grid h-10 w-10 place-items-center rounded-full border-4 border-white bg-tealTrust text-white shadow-sm">
                                                <x-dynamic-component :component="$statusIcons[$inquiry->status] ?? 'heroicon-o-clock'" class="h-5 w-5" />
                                            </span>
                                            <h4 class="font-bold text-navyDeep">{{ __('site.statuses.' . $inquiry->status) }}</h4>
                                        </li>
                                    @endforelse
                                </ol>
                            </div>
                        </article>
                    @else
                        <div class="mt-8 rounded-lg border border-red-200 bg-white p-7 text-center shadow-[0_14px_34px_rgba(15,42,64,0.06)]">
                            <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-red-50 text-red-600">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6" />
                            </span>
                            <h2 class="mt-4 text-xl font-bold text-navyDeep">{{ __('site.pages.track_not_found_title') }}</h2>
                            <p class="mx-auto mt-2 max-w-lg leading-7 text-slate-600">{{ __('site.common.not_found_ref') }}</p>
                        </div>
                    @endif
                @endisset
            </div>
        </div>
    </section>
</x-layouts.app>
