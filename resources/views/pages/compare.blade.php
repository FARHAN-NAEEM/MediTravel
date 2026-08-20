<x-layouts.app :title="__('site.pages.compare') . ' - MediTravel'">
    <section class="container-page py-10">
        <h1 class="section-title">{{ __('site.pages.compare') }}</h1>
        <form method="POST" class="mt-6 grid gap-4 rounded-lg bg-white p-4 shadow-sm">
            @csrf
            <select name="treatment_id" class="rounded-md border-slate-200" required>
                <option value="">{{ __('site.pages.select_treatment') }}</option>
                @foreach ($treatments as $treatment)
                    <option value="{{ $treatment->id }}" @selected(($selectedTreatment ?? null) == $treatment->id)>{{ $treatment->name }}</option>
                @endforeach
            </select>
            <div class="grid gap-2 md:grid-cols-2">
                @foreach ($hospitals as $hospital)
                    <label class="flex items-center gap-3 rounded-md border border-slate-200 p-3">
                        <input type="checkbox" name="hospital_ids[]" value="{{ $hospital->id }}" @checked(in_array($hospital->id, $selectedHospitals ?? []))>
                        <span>{{ $hospital->name }}</span>
                    </label>
                @endforeach
            </div>
            <button class="btn-primary justify-self-start">{{ __('site.pages.compare') }}</button>
        </form>
        <div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
            @foreach ($selected as $hospital)
                <div class="card p-5">
                    <div class="text-lg font-bold">{{ $hospital->name }}</div>
                    <div class="text-sm text-slate-600">{{ $hospital->city->name }}</div>
                    <div class="mt-4 text-sm text-slate-500">{{ $hospital->accreditation }}</div>
                    @foreach ($hospital->treatmentCosts as $cost)
                        <div class="mt-5 rounded-md bg-cloud p-3 font-bold text-tealTrust">{{ $cost->currency }} {{ number_format($cost->cost_min) }} - {{ number_format($cost->cost_max) }}</div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </section>
</x-layouts.app>
