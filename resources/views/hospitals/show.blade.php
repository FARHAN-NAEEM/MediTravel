<x-layouts.app title="{{ $hospital->localized('name') }} - Asian Health Connect">
    <section class="container-page py-8 md:py-12">
        <a href="{{ route('hospitals.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-tealTrust"><x-heroicon-o-arrow-left class="h-4 w-4" />{{ __('hospitals.all') }}</a>
        <div class="hospital-profile mt-8">
            <div class="min-w-0">
                <div class="hospital-profile__heading">
                    @if ($hospital->logoUrl())<img src="{{ $hospital->logoUrl() }}" alt="{{ $hospital->group?->name ?? $hospital->name }}" class="hospital-profile__logo {{ $hospital->logoNeedsDarkBackground() ? 'hospital-logo--reversed' : '' }}" width="260" height="150">@endif
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-tealTrust">{{ __('hospitals.care_types.'.$hospital->care_type) }}</p>
                        <h1 class="mt-3 text-2xl font-bold leading-relaxed md:text-3xl">{{ $hospital->localized('name') }}</h1>
                        <p class="mt-3 flex items-center gap-2 text-slate-600"><x-heroicon-o-map-pin class="h-5 w-5 shrink-0" />{{ $hospital->locationLabel() }}</p>
                        <a href="#appointment" class="btn-primary mt-5 gap-2 lg:hidden"><x-heroicon-o-calendar-days class="h-5 w-5" />{{ __('hospitals.support') }}</a>
                    </div>
                </div>
                @if ($hospital->photoUrl())<img class="mt-8 aspect-[16/9] w-full rounded-lg object-cover" src="{{ $hospital->photoUrl() }}" alt="{{ $hospital->name }}" width="800" height="450">@endif
                @if ($hospital->localized('description'))<h2 class="mt-8 text-xl font-bold">{{ __('hospitals.overview') }}</h2><p class="mt-3 whitespace-pre-line leading-8 text-slate-600">{{ $hospital->localized('description') }}</p>@endif
                @if ($hospital->accreditation)<p class="mt-5 flex items-center gap-2 text-sm text-tealTrust"><x-heroicon-o-shield-check class="h-5 w-5" />{{ $hospital->accreditation }}</p>@endif
                <div class="mt-8 border-y border-slate-200 py-6"><h2 class="text-xl font-bold">{{ __('hospitals.our_support') }}</h2><ol class="mt-4 space-y-4">@foreach (__('hospitals.steps') as $step)<li class="flex items-start gap-3 text-slate-600"><span class="font-bold text-tealTrust">0{{ $loop->iteration }}</span>{{ $step }}</li>@endforeach</ol></div>
                @if ($hospital->source_url)<a href="{{ $hospital->source_url }}" rel="noopener noreferrer" target="_blank" class="mt-5 inline-flex items-center gap-2 text-sm text-tealTrust underline underline-offset-4">{{ __('hospitals.source') }}<x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" /></a>@endif
                @if ($hospital->directory_reviewed_at)<p class="mt-2 text-xs text-slate-500">{{ __('hospitals.reviewed', ['date' => $hospital->directory_reviewed_at->format('d M Y')]) }}</p>@endif
            </div>
            <aside id="appointment" class="min-w-0">
                <h2 class="mb-4 text-xl font-bold">{{ __('hospitals.support') }}</h2>
                @include('pages.partials.inquiry-form', ['type' => 'hospital', 'hospital' => $hospital, 'submitLabel' => __('hospitals.send_request')])
                <p class="mt-3 text-xs leading-6 text-slate-500">{{ __('hospitals.appointment_note') }}</p>
                <a class="btn-secondary mt-4 w-full gap-2" href="https://wa.me/{{ app(\App\Services\SiteContactService::class)->primaryWhatsappNumber() }}?text={{ rawurlencode(__('hospitals.whatsapp_message', ['hospital' => $hospital->name, 'city' => $hospital->city->name])) }}" target="_blank" rel="noopener noreferrer"><x-heroicon-o-chat-bubble-left-right class="h-5 w-5" />{{ __('hospitals.whatsapp') }}</a>
            </aside>
        </div>
        <h2 class="mt-12 border-t border-slate-200 pt-8 text-2xl font-bold">{{ __('site.nav.doctors') }}</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-3">
            @forelse ($hospital->doctors as $doctor)<a href="{{ route('doctors.show', $doctor) }}" class="card p-5"><h3 class="font-bold">{{ $doctor->localized('name') }}</h3><p class="mt-2 text-sm text-slate-600">{{ $doctor->department->localized('name') }}</p></a>
            @empty<p class="col-span-full leading-7 text-slate-600">{{ __('hospitals.doctors_empty') }}</p>@endforelse
        </div>
        <p class="mt-10 text-xs leading-6 text-slate-500">{{ __('hospitals.directory_note') }}</p>
    </section>
</x-layouts.app>
