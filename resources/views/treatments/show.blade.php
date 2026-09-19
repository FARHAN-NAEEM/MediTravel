<x-layouts.app :title="$treatment->localized('name') . ' - Asian Health Connect'" :description="$treatment->localized('description')">
    <section class="container-page py-8 md:py-12">
        <a href="{{ route('treatments.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-tealTrust">
            <x-heroicon-o-arrow-left class="h-4 w-4" aria-hidden="true" />{{ __('treatments.back') }}
        </a>
        <div class="mt-7 grid items-start gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] lg:gap-12">
            <article class="min-w-0">
                <div class="treatment-detail__heading">
                    <img src="{{ $treatment->imageUrl() }}" alt="" width="512" height="512" class="treatment-detail__image">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-tealTrust">{{ $treatment->department?->localized('name') }}</p>
                        <h1 class="mt-3 text-3xl font-bold leading-snug">{{ $treatment->localized('name') }}</h1>
                        @if ($treatment->department)
                            <a href="{{ route('doctors.index', ['department' => $treatment->department->slug]) }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-tealTrust underline underline-offset-4">
                                {{ __('treatments.specialist') }}<x-heroicon-o-arrow-up-right class="h-4 w-4 shrink-0" aria-hidden="true" />
                            </a>
                        @endif
                    </div>
                </div>
                <h2 class="mt-8 text-xl font-bold">{{ __('treatments.overview') }}</h2>
                <p class="mt-3 whitespace-pre-line leading-8 text-slate-600">{{ $treatment->localized('description') }}</p>
                <div class="mt-8 border-y border-slate-200 py-6">
                    <h2 class="text-xl font-bold">{{ __('treatments.preparation_title') }}</h2>
                    <ul class="mt-4 space-y-4">
                        @foreach (__('treatments.preparation') as $item)
                            <li class="flex items-start gap-3 text-sm leading-7 text-slate-600">
                                <x-heroicon-o-check-circle class="mt-1 h-5 w-5 shrink-0 text-tealTrust" aria-hidden="true" />{{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                <h2 class="mt-8 text-xl font-bold">{{ __('site.common.estimated_cost_range') }}</h2>
                @if ($treatment->costs->isNotEmpty())
                    <div class="mt-4 divide-y divide-slate-200">
                        @foreach ($treatment->costs as $cost)
                            <div class="flex flex-wrap items-start justify-between gap-3 py-5">
                                <div class="min-w-0">
                                    <a href="{{ route('hospitals.show', $cost->hospital) }}" class="font-semibold hover:text-tealTrust">{{ $cost->hospital->name }}</a>
                                    <p class="mt-1 text-sm leading-6 text-slate-500">{{ $cost->notes }}</p>
                                </div>
                                <p class="font-bold text-tealTrust">{{ $cost->currency }} {{ number_format($cost->cost_min) }} - {{ number_format($cost->cost_max) }}</p>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-sm leading-7 text-slate-500">{{ __('treatments.cost_note') }}</p>
                @else
                    <p class="mt-4 font-semibold text-tealTrust">{{ __('treatments.no_costs') }}</p>
                    <p class="mt-2 text-sm leading-7 text-slate-600">{{ __('treatments.no_costs_body') }}</p>
                @endif
            </article>
            <aside class="min-w-0" aria-label="{{ __('treatments.inquiry') }}">
                <h2 class="text-xl font-bold">{{ __('treatments.plan_title') }}</h2>
                <p class="mb-5 mt-3 text-sm leading-7 text-slate-600">{{ __('treatments.plan_body') }}</p>
                @include('pages.partials.inquiry-form', ['type' => 'cost', 'treatment' => $treatment])
            </aside>
        </div>
        @if ($relatedTreatments->isNotEmpty())
            <div class="mt-14 border-t border-slate-200 pt-8">
                <h2 class="mb-6 text-2xl font-bold">{{ __('treatments.related') }}</h2>
                <div class="treatment-grid">
                    @foreach ($relatedTreatments as $related)
                        <x-treatment-card :treatment="$related" />
                    @endforeach
                </div>
            </div>
        @endif
        <p class="mt-9 border-t border-slate-200 pt-5 text-sm leading-7 text-slate-500">{{ __('treatments.medical_note') }}</p>
    </section>
</x-layouts.app>
