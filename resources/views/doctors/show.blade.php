<x-layouts.app title="{{ $doctor->name }} - Asian Health Connect">
    <section class="container-page grid gap-8 py-10 lg:grid-cols-[1fr_.8fr]">
        <div class="card p-6">
            <div class="grid gap-6 md:grid-cols-[140px_1fr]">
                <div class="grid h-32 w-32 place-items-center rounded-lg bg-tealTrust/10 text-4xl font-bold text-tealTrust">{{ Str::of($doctor->name)->after('Dr. ')->substr(0, 1) }}</div>
                <div>
                    <h1 class="text-3xl font-bold">{{ $doctor->name }}</h1>
                    <p class="mt-2 text-slate-600">{{ $doctor->designation }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $doctor->qualifications }} · {{ $doctor->experience_years }} years experience</p>
                    <div class="mt-4 inline-flex rounded-md bg-tealTrust/10 px-3 py-2 text-sm font-semibold text-tealTrust">{{ $doctor->department->name }}</div>
                </div>
            </div>
            <div class="mt-8 leading-8 text-slate-700">{{ $doctor->bio }}</div>
            <div class="mt-8 rounded-lg bg-cloud p-5">
                <div class="font-bold">{{ $doctor->hospital->name }}</div>
                <div class="text-sm text-slate-600">{{ $doctor->hospital->city->name }}, {{ $doctor->hospital->country->name }}</div>
            </div>
        </div>
        <div>
            <h2 class="mb-4 text-xl font-bold">{{ __('site.common.book_appointment') }}</h2>
            @include('pages.partials.inquiry-form', ['type' => 'appointment', 'doctor' => $doctor, 'hospital' => $doctor->hospital])
        </div>
    </section>
</x-layouts.app>
