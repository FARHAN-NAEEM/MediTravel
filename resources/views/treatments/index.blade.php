<x-layouts.app :title="__('site.pages.treatments') . ' - Asian Health Connect'">
    <section class="container-page py-10">
        <h1 class="section-title">{{ __('site.pages.treatments') }}</h1>
        <div class="mt-8 grid gap-5 md:grid-cols-3">
            @foreach ($treatments as $treatment)
                <a href="{{ route('treatments.show', $treatment) }}" class="card p-5 transition hover:shadow-md">
                    <div class="text-lg font-bold">{{ $treatment->name }}</div>
                    <div class="mt-1 text-sm text-slate-600">{{ $treatment->department->name }}</div>
                    <p class="mt-4 line-clamp-3 text-sm leading-6 text-slate-600">{{ $treatment->description }}</p>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $treatments->links() }}</div>
    </section>
</x-layouts.app>
