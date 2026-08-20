<x-layouts.app :title="__('site.pages.blog') . ' - MediTravel'">
    <section class="container-page py-10">
        <h1 class="section-title">{{ __('site.pages.blog') }}</h1>
        <div class="mt-8 grid gap-5 md:grid-cols-3">
            @foreach ($posts as $post)
                <a href="{{ route('blog.show', $post) }}" class="card p-5">
                    <div class="text-sm font-semibold text-tealTrust">{{ $post->category?->name }}</div>
                    <div class="mt-3 text-lg font-bold">{{ $post->title }}</div>
                    <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">{{ $post->meta_description }}</p>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
    </section>
</x-layouts.app>
