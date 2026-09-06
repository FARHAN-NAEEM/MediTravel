<x-layouts.app title="{{ $service->name }} - Asian Health Connect">
    <section class="container-page grid gap-8 py-10 lg:grid-cols-[1fr_.75fr]">
        <div>
            <h1 class="section-title">{{ $service->name }}</h1>
            <p class="mt-5 leading-8 text-slate-700">{{ $service->body }}</p>
            <x-document-preparation-checklist
                :documents="$service->requiredDocuments"
                storage-key="service-{{ $service->id }}"
            />
        </div>
        <div>@include('pages.partials.inquiry-form', ['type' => $service->slug])</div>
    </section>
</x-layouts.app>
