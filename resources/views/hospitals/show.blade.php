<x-layouts.app title="{{ $hospital->name }} - Asian Health Connect">
    <section class="container-page py-10">
        <div class="grid gap-8 lg:grid-cols-[1fr_.75fr]">
            <div>
                <div class="h-64 rounded-lg bg-gradient-to-br from-tealTrust/20 via-white to-accent/20"></div>
                <h1 class="mt-8 text-3xl font-bold">{{ $hospital->name }}</h1>
                <p class="mt-2 text-slate-600">{{ $hospital->city->name }}, {{ $hospital->country->name }} · {{ $hospital->accreditation }}</p>
                <p class="mt-5 leading-8 text-slate-700">{{ $hospital->description }}</p>
            </div>
            <div>
                @include('pages.partials.inquiry-form', ['type' => 'hospital', 'hospital' => $hospital])
            </div>
        </div>
        <h2 class="mt-12 text-2xl font-bold">{{ __('site.nav.doctors') }}</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-3">
            @foreach ($hospital->doctors as $doctor)
                <a href="{{ route('doctors.show', $doctor) }}" class="card p-5">
                    <div class="font-bold">{{ $doctor->name }}</div>
                    <div class="text-sm text-slate-600">{{ $doctor->department->name }}</div>
                </a>
            @endforeach
        </div>
    </section>
</x-layouts.app>
