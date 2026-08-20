<x-layouts.app :title="__('site.pages.hospital_directory') . ' - MediTravel'">
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
        <div class="mt-8 grid gap-5 md:grid-cols-3">
            @foreach ($hospitals as $hospital)
                <a href="{{ route('hospitals.show', $hospital) }}" class="card overflow-hidden transition hover:shadow-md">
                    <div class="h-40 bg-gradient-to-br from-tealTrust/20 via-white to-accent/20"></div>
                    <div class="p-5">
                        <div class="text-lg font-bold">{{ $hospital->name }}</div>
                        <div class="mt-1 text-sm text-slate-600">{{ $hospital->city->name }}, {{ $hospital->city->country->name }}</div>
                        <div class="mt-3 text-sm text-tealTrust">{{ $hospital->accreditation }}</div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $hospitals->links() }}</div>
    </section>
</x-layouts.app>
