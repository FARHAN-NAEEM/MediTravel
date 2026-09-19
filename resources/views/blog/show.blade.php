<x-layouts.app title="{{ $post->localized('meta_title') ?: $post->localized('title') }} - Asian Health Connect" :description="$post->localized('meta_description')">
    <article class="container-page max-w-3xl py-10">
        <div class="text-sm font-semibold text-tealTrust">{{ $post->category?->localized('name') }}</div>
        <h1 class="readable-heading mt-3 text-3xl font-bold leading-snug sm:text-4xl">{{ $post->localized('title') }}</h1>
        <div class="prose prose-slate mt-8 max-w-none whitespace-pre-line leading-8">{{ $post->localized('body') }}</div>
    </article>
</x-layouts.app>
