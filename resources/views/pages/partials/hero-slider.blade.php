<section
    x-data="heroSlider({{ $heroImages->count() }}, {{ $heroSlideInterval * 1000 }})"
    class="hero-slider"
    role="region"
    aria-roledescription="carousel"
    aria-label="{{ __('slider.label') }}"
    @keydown.arrow-left.prevent="go(active - 1)"
    @keydown.arrow-right.prevent="go(active + 1)"
    @visibilitychange.document="hidden = document.hidden"
    @touchstart.passive="touchStart($event)"
    @touchend.passive="touchEnd($event)"
    @touchcancel="touch = null"
>
    <div class="hero-slider__slides" :aria-live="reducedMotion ? 'polite' : 'off'">
        @forelse ($heroImages as $index => $slide)
            <figure
                class="hero-slider__slide {{ $index > 0 ? 'hero-slider__slide--inactive' : '' }}"
                :class="{ 'hero-slider__slide--inactive': active !== {{ $index }} }"
                :aria-hidden="active !== {{ $index }}"
                aria-hidden="{{ $index > 0 ? 'true' : 'false' }}"
                role="group"
                aria-roledescription="slide"
                aria-label="{{ $index + 1 }} / {{ $heroImages->count() }}"
            >
                <div class="hero-slider__image">
                    <img
                        src="{{ $slide->imageUrl() }}"
                        alt="{{ $slide->alt_text ?: $slide->localizedContent('heading') ?: $slide->title ?: __('slider.label') }}"
                        class="{{ $slide->image_fit === 'cover' ? 'object-cover' : 'object-contain' }}"
                        width="960"
                        height="600"
                        loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                        fetchpriority="{{ $index === 0 ? 'high' : 'auto' }}"
                        decoding="async"
                    >
                </div>
                @if ($slide->localizedContent('heading') || $slide->localizedContent('body'))
                    <figcaption class="hero-slider__caption">
                        @if ($slide->localizedContent('heading'))
                            <h2>{{ $slide->localizedContent('heading') }}</h2>
                        @endif
                        @if ($slide->localizedContent('body'))
                            <p>{{ $slide->localizedContent('body') }}</p>
                        @endif
                    </figcaption>
                @endif
            </figure>
        @empty
            <img class="aspect-[4/3] w-full object-cover" src="https://images.unsplash.com/photo-1586773860418-d37222d8fce3?auto=format&fit=crop&w=1200&q=80" alt="{{ __('slider.label') }}">
        @endforelse
    </div>
    @if ($heroImages->count() > 1)
        <div class="hero-slider__controls" x-cloak>
            <div class="hero-slider__indicators" role="group" aria-label="{{ __('slider.label') }}">
                @foreach ($heroImages as $index => $slide)
                    <button
                        type="button"
                        class="hero-slider__indicator"
                        x-show="{{ $index }} >= indicatorStart && {{ $index }} < indicatorStart + 5"
                        :class="{ 'hero-slider__indicator--active': active === {{ $index }} }"
                        :aria-current="active === {{ $index }} ? 'true' : 'false'"
                        @click="go({{ $index }})"
                        aria-label="{{ __('slider.go_to', ['number' => $index + 1]) }}"
                        title="{{ __('slider.go_to', ['number' => $index + 1]) }}"
                    ><span aria-hidden="true"></span></button>
                @endforeach
            </div>
            <div class="flex shrink-0 gap-2">
                <button type="button" @click="go(active - 1)" class="hero-slider__button" aria-label="{{ __('slider.previous') }}" title="{{ __('slider.previous') }}"><x-heroicon-o-chevron-left class="h-5 w-5" /></button>
                <button type="button" @click="go(active + 1)" class="hero-slider__button" aria-label="{{ __('slider.next') }}" title="{{ __('slider.next') }}"><x-heroicon-o-chevron-right class="h-5 w-5" /></button>
            </div>
        </div>
    @endif
</section>
