<form method="POST" action="<?php echo e(route('inquiries.store')); ?>" class="card grid gap-4 p-5">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="type" value="<?php echo e($type ?? 'appointment'); ?>">
    <input type="hidden" name="doctor_id" value="<?php echo e($doctor->id ?? ''); ?>">
    <input type="hidden" name="hospital_id" value="<?php echo e($hospital->id ?? ''); ?>">
    <input type="hidden" name="treatment_id" value="<?php echo e($treatment->id ?? ''); ?>">
    <input class="rounded-md border-slate-200" name="name" placeholder="<?php echo e(__('site.form.name')); ?>" required>
    <div class="grid gap-4 sm:grid-cols-2">
        <input class="rounded-md border-slate-200" name="phone" placeholder="<?php echo e(__('site.form.phone')); ?>" required>
        <input class="rounded-md border-slate-200" name="whatsapp" placeholder="<?php echo e(__('site.form.whatsapp')); ?>">
    </div>
    <textarea class="rounded-md border-slate-200" name="message" rows="4" placeholder="<?php echo e(__('site.form.message')); ?>"></textarea>
    <button class="btn-primary"><?php echo e(__('site.form.submit')); ?></button>
</form>
<?php /**PATH G:\MERN\MediTravel\resources\views/pages/partials/inquiry-form.blade.php ENDPATH**/ ?>