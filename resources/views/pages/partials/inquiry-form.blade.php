<form method="POST" action="{{ route('inquiries.store') }}" class="card grid gap-4 p-5">
    @csrf
    <input type="hidden" name="type" value="{{ $type ?? 'appointment' }}">
    <input type="hidden" name="doctor_id" value="{{ $doctor->id ?? '' }}">
    <input type="hidden" name="hospital_id" value="{{ $hospital->id ?? '' }}">
    <input type="hidden" name="treatment_id" value="{{ $treatment->id ?? '' }}">
    <input class="rounded-md border-slate-200" name="name" placeholder="{{ __('site.form.name') }}" required>
    <div class="grid gap-4 sm:grid-cols-2">
        <input class="rounded-md border-slate-200" name="phone" placeholder="{{ __('site.form.phone') }}" required>
        <input class="rounded-md border-slate-200" name="whatsapp" placeholder="{{ __('site.form.whatsapp') }}">
    </div>
    <textarea class="rounded-md border-slate-200" name="message" rows="4" placeholder="{{ __('site.form.message') }}"></textarea>
    <button class="btn-primary">{{ __('site.form.submit') }}</button>
</form>
