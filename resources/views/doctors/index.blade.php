<x-layouts.app :title="__('site.pages.doctor_directory') . ' - MediTravel'">
    <section class="container-page py-10">
        <h1 class="section-title">{{ __('site.pages.doctor_directory') }}</h1>
        <form class="mt-6 grid gap-3 rounded-lg bg-white p-4 shadow-sm md:grid-cols-4">
            <input name="q" value="{{ request('q') }}" class="rounded-md border-slate-200 md:col-span-2" placeholder="{{ __('site.common.search_doctor') }}">
            <select name="department" class="rounded-md border-slate-200">
                <option value="">{{ __('site.common.all_departments') }}</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->slug }}" @selected(request('department') === $department->slug)>{{ $department->name }}</option>
                @endforeach
            </select>
            <button class="btn-primary">{{ __('site.common.filter') }}</button>
        </form>
        <div class="mt-8 grid gap-5 md:grid-cols-3">
            @foreach ($doctors as $doctor)
                <a href="{{ route('doctors.show', $doctor) }}" class="card p-5 transition hover:shadow-md">
                    <div class="mb-4 grid h-28 w-28 place-items-center rounded-lg bg-tealTrust/10 text-3xl font-bold text-tealTrust">{{ Str::of($doctor->name)->after('Dr. ')->substr(0, 1) }}</div>
                    <div class="text-lg font-bold">{{ $doctor->name }}</div>
                    <div class="text-sm text-slate-600">{{ $doctor->designation }}</div>
                    <div class="mt-3 text-sm text-slate-500">{{ $doctor->hospital->name }} · {{ $doctor->department->name }}</div>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $doctors->links() }}</div>
    </section>
</x-layouts.app>
