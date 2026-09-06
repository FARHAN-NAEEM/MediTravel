<x-layouts.app :title="__('site.pages.health_packages') . ' - Asian Health Connect'">
    <section class="container-page py-10">
        <h1 class="section-title">{{ __('site.pages.health_packages') }}</h1>
        <div class="mt-8 grid gap-5 md:grid-cols-3">
            @foreach ($packages as $package)
                <div class="card p-5">
                    <div class="text-lg font-bold">{{ $package->name }}</div>
                    <div class="mt-1 text-sm text-slate-600">{{ $package->hospital->name }}</div>
                    <div class="mt-4 text-2xl font-bold text-tealTrust">{{ $package->currency }} {{ number_format($package->price) }}</div>
                    <ul class="mt-4 grid gap-2 text-sm text-slate-600">
                        @foreach ($package->includes ?? [] as $include)
                            <li>{{ $include }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
        <div class="mt-8">{{ $packages->links() }}</div>
    </section>
</x-layouts.app>
