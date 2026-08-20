<x-layouts.app :title="__('site.pages.services') . ' - MediTravel'">
    <section class="container-page py-10">
        <h1 class="section-title">{{ __('site.pages.services') }}</h1>
        <div class="mt-8 grid gap-5 md:grid-cols-3">
            @foreach ($services as $service)
                <a href="{{ route('services.show', $service) }}" class="card p-5 transition hover:shadow-md">
                    <div class="text-lg font-bold">{{ $service->name }}</div>
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $service->short_desc }}</p>
                </a>
            @endforeach
        </div>
    </section>
</x-layouts.app>
