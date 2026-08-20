<x-layouts.app :title="__('site.pages.reviews') . ' - MediTravel'">
    <section class="container-page py-10">
        <h1 class="section-title">{{ __('site.pages.reviews') }}</h1>
        <div class="mt-8 grid gap-5 md:grid-cols-3">
            @foreach ($reviews as $review)
                <div class="card p-5">
                    <div class="text-lg font-bold">{{ $review->patient_name }}</div>
                    <div class="mt-1 text-sm text-accent">{{ str_repeat('*', $review->rating) }}</div>
                    <p class="mt-4 leading-7 text-slate-600">{{ $review->body }}</p>
                    <div class="mt-4 text-sm text-slate-500">{{ $review->treatment }} · {{ $review->hospital?->name }}</div>
                </div>
            @endforeach
        </div>
        <div class="mt-8">{{ $reviews->links() }}</div>
    </section>
</x-layouts.app>
