<x-layouts.app :title="__('site.pages.faq') . ' - Asian Health Connect'">
    <section class="container-page max-w-4xl py-10">
        <h1 class="section-title">{{ __('site.pages.faq') }}</h1>
        <div class="mt-8 grid gap-4">
            @foreach ($faqs as $faq)
                <details class="card p-5">
                    <summary class="cursor-pointer font-bold">{{ $faq->question }}</summary>
                    <p class="mt-4 leading-7 text-slate-600">{{ $faq->answer }}</p>
                </details>
            @endforeach
        </div>
    </section>
</x-layouts.app>
