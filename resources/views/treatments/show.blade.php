<x-layouts.app title="{{ $treatment->name }} - MediTravel">
    <section class="container-page grid gap-8 py-10 lg:grid-cols-[1fr_.75fr]">
        <div>
            <h1 class="text-3xl font-bold">{{ $treatment->name }}</h1>
            <p class="mt-2 text-tealTrust">{{ $treatment->department->name }}</p>
            <p class="mt-5 leading-8 text-slate-700">{{ $treatment->description }}</p>
            <h2 class="mt-10 text-2xl font-bold">{{ __('site.common.estimated_cost_range') }}</h2>
            <div class="mt-5 grid gap-4">
                @foreach ($treatment->costs as $cost)
                    <div class="card flex items-center justify-between gap-4 p-5">
                        <div>
                            <div class="font-bold">{{ $cost->hospital->name }}</div>
                            <div class="text-sm text-slate-500">{{ $cost->notes }}</div>
                        </div>
                        <div class="text-right font-bold text-tealTrust">{{ $cost->currency }} {{ number_format($cost->cost_min) }} - {{ number_format($cost->cost_max) }}</div>
                    </div>
                @endforeach
            </div>
        </div>
        <div>
            @include('pages.partials.inquiry-form', ['type' => 'cost', 'treatment' => $treatment])
        </div>
    </section>
</x-layouts.app>
