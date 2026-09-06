<x-layouts.app title="{{ $page?->title ?? 'Page' }} - Asian Health Connect">
    <section class="container-page max-w-3xl py-10">
        <h1 class="section-title">{{ $page?->title ?? __('site.common.coming_soon') }}</h1>
        <div class="mt-6 leading-8 text-slate-700">{{ $page?->body ?? __('site.common.admin_content_ready') }}</div>
    </section>
</x-layouts.app>
