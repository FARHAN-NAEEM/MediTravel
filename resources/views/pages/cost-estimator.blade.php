<x-layouts.app :title="__('site.pages.cost_estimator') . ' - Asian Health Connect'">
    <section class="container-page py-10">
        <h1 class="section-title">{{ __('site.pages.cost_estimator') }}</h1>
        <p class="mt-3 max-w-2xl text-slate-600">{{ __('site.pages.cost_estimator_sub') }}</p>
        <form method="POST" class="mt-6 grid gap-3 rounded-lg bg-white p-4 shadow-sm md:grid-cols-[1fr_1fr_auto]">
            @csrf
            <select name="treatment_id" class="rounded-md border-slate-200" required>
                <option value="">{{ __('site.pages.select_treatment') }}</option>
                @foreach ($treatments as $treatment)
                    <option value="{{ $treatment->id }}" @selected(($selectedTreatment ?? null) == $treatment->id)>{{ $treatment->name }}</option>
                @endforeach
            </select>
            <select name="hospital_id" class="rounded-md border-slate-200">
                <option value="">{{ __('site.common.all_hospitals') }}</option>
                @foreach ($hospitals as $hospital)
                    <option value="{{ $hospital->id }}" @selected(($selectedHospital ?? null) == $hospital->id)>{{ $hospital->name }}</option>
                @endforeach
            </select>
            <button class="btn-primary">{{ __('site.pages.show_range') }}</button>
        </form>
        <div class="mt-8 grid gap-4">
            @forelse ($results as $cost)
                <div class="card grid gap-4 p-5 md:grid-cols-[1fr_auto]">
                    <div>
                        <div class="text-lg font-bold">{{ $cost->treatment->name }}</div>
                        <div class="text-sm text-slate-600">{{ $cost->hospital->name }} · {{ $cost->hospital->city->name }}</div>
                        <p class="mt-2 text-sm text-slate-500">{{ $cost->notes }}</p>
                    </div>
                    <div class="text-2xl font-bold text-tealTrust">{{ $cost->currency }} {{ number_format($cost->cost_min) }} - {{ number_format($cost->cost_max) }}</div>
                </div>
            @empty
                <div class="rounded-lg border border-dashed border-slate-300 p-8 text-center text-slate-500">{{ __('site.pages.choose_treatment') }}</div>
            @endforelse
        </div>
    </section>
</x-layouts.app>
