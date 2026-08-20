<x-layouts.app :title="__('site.pages.visa_title') . ' - MediTravel'">
    <section class="container-page grid gap-8 py-10 lg:grid-cols-[1fr_.75fr]">
        <div>
            <h1 class="section-title">{{ __('site.pages.visa_title') }}</h1>
            <p class="mt-3 leading-8 text-slate-600">{{ __('site.pages.visa_sub') }}</p>
            <div class="mt-8 grid gap-3">
                @forelse ($visaDocuments as $document)
                    <label class="flex items-center gap-3 rounded-md bg-white p-4 shadow-sm">
                        <input type="checkbox" class="rounded border-slate-300 text-tealTrust">
                        <span>
                            <span class="block font-semibold">{{ $document->localizedTitle() }}</span>
                            @if ($document->localizedDescription())
                                <span class="mt-1 block text-sm text-slate-500">{{ $document->localizedDescription() }}</span>
                            @endif
                        </span>
                    </label>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 p-6 text-slate-500">{{ __('site.common.admin_content_ready') }}</div>
                @endforelse
            </div>
        </div>
        <div>
            @include('pages.partials.inquiry-form', ['type' => 'visa'])
        </div>
    </section>
</x-layouts.app>
