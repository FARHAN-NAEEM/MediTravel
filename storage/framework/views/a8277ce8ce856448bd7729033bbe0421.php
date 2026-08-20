<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => __('site.pages.doctor_directory') . ' - MediTravel']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('site.pages.doctor_directory') . ' - MediTravel')]); ?>
    <section class="container-page py-10">
        <h1 class="section-title"><?php echo e(__('site.pages.doctor_directory')); ?></h1>
        <form class="mt-6 grid gap-3 rounded-lg bg-white p-4 shadow-sm md:grid-cols-4">
            <input name="q" value="<?php echo e(request('q')); ?>" class="rounded-md border-slate-200 md:col-span-2" placeholder="<?php echo e(__('site.common.search_doctor')); ?>">
            <select name="department" class="rounded-md border-slate-200">
                <option value=""><?php echo e(__('site.common.all_departments')); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($department->slug); ?>" <?php if(request('department') === $department->slug): echo 'selected'; endif; ?>><?php echo e($department->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
            <button class="btn-primary"><?php echo e(__('site.common.filter')); ?></button>
        </form>
        <div class="mt-8 grid gap-5 md:grid-cols-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('doctors.show', $doctor)); ?>" class="card p-5 transition hover:shadow-md">
                    <div class="mb-4 grid h-28 w-28 place-items-center rounded-lg bg-tealTrust/10 text-3xl font-bold text-tealTrust"><?php echo e(Str::of($doctor->name)->after('Dr. ')->substr(0, 1)); ?></div>
                    <div class="text-lg font-bold"><?php echo e($doctor->name); ?></div>
                    <div class="text-sm text-slate-600"><?php echo e($doctor->designation); ?></div>
                    <div class="mt-3 text-sm text-slate-500"><?php echo e($doctor->hospital->name); ?> · <?php echo e($doctor->department->name); ?></div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="mt-8"><?php echo e($doctors->links()); ?></div>
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
<?php /**PATH G:\MERN\MediTravel\resources\views/doctors/index.blade.php ENDPATH**/ ?>