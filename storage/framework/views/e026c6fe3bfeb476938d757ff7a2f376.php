<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => __('site.home.title')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.home.title'))]); ?>
    <section class="bg-white">
        <div class="container-page grid min-h-[620px] items-center gap-10 py-12 lg:grid-cols-[1.05fr_.95fr]">
            <div>
                <div class="mb-4 inline-flex rounded-md bg-tealTrust/10 px-3 py-2 text-sm font-semibold text-tealTrust"><?php echo e(__('site.home.eyebrow')); ?></div>
                <h1 class="max-w-3xl text-4xl font-extrabold leading-tight tracking-normal text-navyDeep md:text-6xl"><?php echo e(__('site.home.headline')); ?></h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600"><?php echo e(__('site.home.subhead')); ?></p>
                <form action="<?php echo e(route('doctors.index')); ?>" class="mt-8 grid gap-3 rounded-lg border border-slate-200 bg-cloud p-3 shadow-sm sm:grid-cols-[1fr_auto]">
                    <input name="q" class="rounded-md border-slate-200" placeholder="<?php echo e(__('site.home.search_placeholder')); ?>">
                    <button class="btn-primary"><?php echo e(__('site.home.search')); ?></button>
                </form>
                <div class="mt-8 grid grid-cols-3 gap-4">
                    <div><div class="text-3xl font-bold text-tealTrust"><?php echo e($stats['hospitals']); ?>+</div><div class="text-sm text-slate-500"><?php echo e(__('site.home.hospitals')); ?></div></div>
                    <div><div class="text-3xl font-bold text-tealTrust"><?php echo e($stats['doctors']); ?>+</div><div class="text-sm text-slate-500"><?php echo e(__('site.home.doctors')); ?></div></div>
                    <div><div class="text-3xl font-bold text-tealTrust"><?php echo e($stats['patients']); ?>+</div><div class="text-sm text-slate-500"><?php echo e(__('site.home.patients_guided')); ?></div></div>
                </div>
            </div>
            <div
                x-data="{ active: 0, images: <?php echo \Illuminate\Support\Js::from($heroImages->map(fn ($image) => ['url' => $image->imageUrl(), 'alt' => $image->alt_text ?: $image->title ?: 'Hospital image'])->values())->toHtml() ?> }"
                x-init="if (images.length > 1) setInterval(() => active = (active + 1) % images.length, 4500)"
                class="relative aspect-[4/3] overflow-hidden rounded-lg shadow-2xl"
            >
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($heroImages->isNotEmpty()): ?>
                    <template x-for="(image, index) in images" :key="image.url">
                        <img
                            x-show="active === index"
                            x-transition.opacity.duration.700ms
                            class="absolute inset-0 h-full w-full object-cover"
                            :src="image.url"
                            :alt="image.alt"
                        >
                    </template>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($heroImages->count() > 1): ?>
                        <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $heroImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button
                                    type="button"
                                    class="h-2.5 w-2.5 rounded-full border border-white/70"
                                    :class="active === <?php echo e($index); ?> ? 'bg-white' : 'bg-white/40'"
                                    @click="active = <?php echo e($index); ?>"
                                    aria-label="Show hero image <?php echo e($index + 1); ?>"
                                ></button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php else: ?>
                    <img class="h-full w-full object-cover" src="https://images.unsplash.com/photo-1586773860418-d37222d8fce3?auto=format&fit=crop&w=1200&q=80" alt="Hospital care team">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>

    <section class="py-14">
        <div class="container-page">
            <div class="mb-8 flex items-end justify-between gap-4">
                <div>
                    <h2 class="section-title"><?php echo e(__('site.home.featured_hospitals')); ?></h2>
                    <p class="mt-2 text-slate-600"><?php echo e(__('site.home.featured_hospitals_sub')); ?></p>
                </div>
                <a href="<?php echo e(route('hospitals.index')); ?>" class="btn-secondary hidden sm:inline-flex"><?php echo e(__('site.home.view_all')); ?></a>
            </div>
            <div class="grid gap-5 md:grid-cols-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $featuredHospitals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hospital): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('hospitals.show', $hospital)); ?>" class="card p-5 transition hover:-translate-y-1 hover:shadow-md">
                        <div class="mb-4 h-36 rounded-md bg-gradient-to-br from-tealTrust/20 to-accent/20"></div>
                        <div class="text-lg font-bold"><?php echo e($hospital->name); ?></div>
                        <div class="mt-1 text-sm text-slate-500"><?php echo e($hospital->city->name); ?>, <?php echo e($hospital->country->name); ?></div>
                        <div class="mt-3 inline-flex rounded-md bg-tealTrust/10 px-2 py-1 text-xs font-semibold text-tealTrust"><?php echo e($hospital->accreditation); ?></div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>

    <section class="bg-white py-14">
        <div class="container-page">
            <h2 class="section-title"><?php echo e(__('site.home.how_it_works')); ?></h2>
            <div class="mt-8 grid gap-5 md:grid-cols-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = __('site.home.steps'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="card p-5">
                        <div class="mb-4 grid h-10 w-10 place-items-center rounded-md bg-accent text-lg font-bold text-white"><?php echo e($loop->iteration); ?></div>
                        <div class="font-bold"><?php echo e($step); ?></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>

    <section class="py-14">
        <div class="container-page grid gap-8 lg:grid-cols-[.8fr_1.2fr]">
            <div>
                <h2 class="section-title"><?php echo e(__('site.home.quick_inquiry')); ?></h2>
                <p class="mt-3 text-slate-600"><?php echo e(__('site.home.quick_inquiry_sub')); ?></p>
            </div>
            <?php echo $__env->make('pages.partials.inquiry-form', ['type' => 'appointment'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </section>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH G:\MERN\MediTravel\resources\views/pages/home.blade.php ENDPATH**/ ?>