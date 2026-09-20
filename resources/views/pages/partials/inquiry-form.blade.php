<form method="POST" action="{{ route('inquiries.store') }}" class="card grid gap-4 p-5">
    @csrf
    <input type="hidden" name="type" value="{{ $type ?? 'appointment' }}">
    <input type="hidden" name="doctor_id" value="{{ $doctor->id ?? '' }}">
    <input type="hidden" name="hospital_id" value="{{ $hospital->id ?? '' }}">
    <input type="hidden" name="treatment_id" value="{{ $treatment->id ?? '' }}">
    <input class="rounded-md border-slate-200" name="name" aria-label="{{ __('site.form.name') }}" autocomplete="name" placeholder="{{ __('site.form.name') }}" required>
    <div class="grid gap-4 sm:grid-cols-2">
        <input class="rounded-md border-slate-200 min-w-0" name="phone" type="tel" aria-label="{{ __('site.form.phone') }}" autocomplete="tel" placeholder="{{ __('site.form.phone') }}" required>
        <input class="rounded-md border-slate-200 min-w-0" name="whatsapp" type="tel" aria-label="{{ __('site.form.whatsapp') }}" placeholder="{{ __('site.form.whatsapp') }}">
    </div>
    <textarea class="rounded-md border-slate-200" name="message" aria-label="{{ __('site.form.message') }}" rows="4" placeholder="{{ __('site.form.message') }}"></textarea>
    <button class="btn-primary gap-2"><x-heroicon-o-paper-airplane class="h-5 w-5 shrink-0" />{{ $submitLabel ?? __('site.form.submit') }}</button>
</form>
