<x-layouts.app title="{{ $post->title }} - MediTravel">
    <article class="container-page max-w-3xl py-10">
        <div class="text-sm font-semibold text-tealTrust">{{ $post->category?->name }}</div>
        <h1 class="mt-3 text-4xl font-bold">{{ $post->title }}</h1>
        <div class="prose prose-slate mt-8 max-w-none leading-8">{{ $post->body }}</div>
    </article>
</x-layouts.app>
