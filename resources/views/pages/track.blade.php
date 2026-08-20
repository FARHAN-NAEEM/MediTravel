<x-layouts.app :title="__('site.pages.track_title') . ' - MediTravel'">
    <section class="container-page grid gap-8 py-10 lg:grid-cols-[.8fr_1.2fr]">
        <div>
            <h1 class="section-title">{{ __('site.pages.track_title') }}</h1>
            <p class="mt-3 text-slate-600">{{ __('site.pages.track_sub') }}</p>
        </div>
        <div>
            <form method="POST" class="card flex gap-3 p-4">
                @csrf
                <input name="ref_number" class="min-w-0 flex-1 rounded-md border-slate-200" placeholder="MT-260821-0001" required>
                <button class="btn-primary">{{ __('site.pages.track_button') }}</button>
            </form>
            @isset($searched)
                <div class="mt-6 card p-5">
                    @if ($inquiry)
                        <div class="text-sm text-slate-500">{{ __('site.common.current_status') }}</div>
                        <div class="mt-1 text-2xl font-bold text-tealTrust">{{ $inquiry->status_label }}</div>
                        <div class="mt-4 text-sm text-slate-600">{{ __('site.common.reference') }}: {{ $inquiry->ref_number }}</div>
                        <div class="mt-6 grid gap-3">
                            @foreach ($inquiry->logs as $log)
                                <div class="rounded-md bg-cloud p-3 text-sm">
                                    <div class="font-semibold">{{ \App\Models\Inquiry::statusLabel($log->status) }}</div>
                                    <div class="text-slate-500">{{ $log->note }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-slate-600">{{ __('site.common.not_found_ref') }}</div>
                    @endif
                </div>
            @endisset
        </div>
    </section>
</x-layouts.app>
