<x-layouts.app :title="__('site.pages.visa_title') . ' - Asian Health Connect'">
    <section class="container-page grid gap-8 py-10 lg:grid-cols-[1fr_.75fr]">
        <div>
            <h1 class="section-title">{{ __('site.pages.visa_title') }}</h1>
            <p class="mt-3 leading-8 text-slate-600">{{ __('site.pages.visa_sub') }}</p>
            <x-document-preparation-checklist :documents="$visaDocuments" storage-key="visa-support" />
        </div>
        <div>
            @include('pages.partials.inquiry-form', ['type' => 'visa'])
        </div>
    </section>
</x-layouts.app>
