<x-layouts.app :title="__('site.pages.contact_title') . ' - MediTravel'">
    <section class="container-page grid gap-8 py-10 lg:grid-cols-[.75fr_1fr]">
        <div>
            <h1 class="section-title">{{ __('site.pages.contact_title') }}</h1>
            <p class="mt-3 text-slate-600">{{ __('site.pages.contact_sub') }}</p>
        </div>
        @include('pages.partials.inquiry-form', ['type' => 'contact'])
    </section>
</x-layouts.app>
