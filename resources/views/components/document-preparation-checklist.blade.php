@props([
    'documents',
    'storageKey',
])

@php
    $documentIds = $documents->pluck('id')->map(fn ($id) => (string) $id)->values();
@endphp

@if ($documents->isNotEmpty())
    <section
        class="mt-8"
        x-data="documentChecklist(@js($storageKey), @js($documentIds))"
        aria-labelledby="document-checklist-{{ md5($storageKey) }}"
    >
        <div class="flex items-start justify-between gap-4">
            <div class="flex min-w-0 items-start gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md bg-tealTrust text-white shadow-sm">
                    <x-heroicon-o-clipboard-document-check class="h-5 w-5" />
                </span>
                <div>
                    <h2 id="document-checklist-{{ md5($storageKey) }}" class="text-xl font-bold text-navyDeep sm:text-2xl">
                        {{ __('site.document_checklist.title') }}
                    </h2>
                    <p class="readable-copy mt-1 text-sm leading-6 text-slate-600">
                        {{ __('site.document_checklist.subtitle') }}
                    </p>
                </div>
            </div>
            <button
                type="button"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-2 py-2 text-xs font-semibold text-slate-500 transition hover:bg-white hover:text-tealTrust disabled:cursor-not-allowed disabled:opacity-40"
                @click="reset()"
                :disabled="completedCount === 0"
                title="{{ __('site.document_checklist.reset') }}"
            >
                <x-heroicon-o-arrow-path class="h-4 w-4" />
                <span class="hidden sm:inline">{{ __('site.document_checklist.reset') }}</span>
            </button>
        </div>

        <div class="mt-5 rounded-md border border-tealTrust/15 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-bold text-navyDeep">
                        <span x-text="completedCount">0</span>
                        <span class="text-slate-400">/</span>
                        <span x-text="totalCount">{{ $documents->count() }}</span>
                        {{ __('site.document_checklist.ready_count_suffix') }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500" x-show="remainingCount > 0">
                        <span x-text="remainingCount"></span> {{ __('site.document_checklist.remaining_suffix') }}
                    </p>
                    <p class="mt-1 text-xs font-semibold text-successCare" x-cloak x-show="remainingCount === 0">
                        {{ __('site.document_checklist.all_ready') }}
                    </p>
                </div>
                <span class="rounded-md bg-tealTrust/10 px-2.5 py-1 text-sm font-bold text-tealTrust">
                    <span x-text="percentReady">0</span>% {{ __('site.document_checklist.ready') }}
                </span>
            </div>
            <div
                class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100"
                role="progressbar"
                aria-valuemin="0"
                aria-valuemax="100"
                :aria-valuenow="percentReady"
                aria-label="{{ __('site.document_checklist.progress') }}"
            >
                <div class="h-full rounded-full bg-tealTrust transition-[width] duration-300 ease-out" :style="`width: ${percentReady}%`"></div>
            </div>
        </div>

        <div class="mt-4 grid gap-3">
            @foreach ($documents as $document)
                <label
                    class="group flex cursor-pointer items-start gap-3 rounded-md border bg-white p-4 shadow-sm transition duration-200 hover:border-tealTrust/35 hover:shadow-md"
                    :class="isReady('{{ $document->id }}') ? 'border-tealTrust/35 bg-tealTrust/5' : 'border-slate-200'"
                >
                    <input
                        type="checkbox"
                        class="mt-0.5 h-5 w-5 shrink-0 rounded border-slate-300 text-tealTrust focus:ring-tealTrust"
                        :checked="isReady('{{ $document->id }}')"
                        @change="toggle('{{ $document->id }}')"
                        aria-label="{{ __('site.document_checklist.mark_ready', ['document' => $document->localizedTitle()]) }}"
                    >
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold text-navyDeep transition group-hover:text-tealTrust">
                            {{ $document->localizedTitle() }}
                        </span>
                        @if ($document->localizedDescription())
                            <span class="readable-copy mt-1 block text-sm leading-6 text-slate-500">
                                {{ $document->localizedDescription() }}
                            </span>
                        @endif
                        <span class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400" x-show="! isReady('{{ $document->id }}')">
                            <x-heroicon-o-clock class="h-3.5 w-3.5" />
                            {{ __('site.document_checklist.not_ready') }}
                        </span>
                        <span class="mt-2 items-center gap-1.5 text-xs font-semibold text-successCare" x-cloak x-show="isReady('{{ $document->id }}')" style="display: inline-flex;">
                            <x-heroicon-o-check-circle class="h-3.5 w-3.5" />
                            {{ __('site.document_checklist.ready') }}
                        </span>
                    </span>
                </label>
            @endforeach
        </div>

        <p class="readable-copy mt-4 flex items-start gap-2 text-xs leading-5 text-slate-500">
            <x-heroicon-o-information-circle class="mt-0.5 h-4 w-4 shrink-0 text-tealTrust" />
            <span>{{ __('site.document_checklist.notice') }}</span>
        </p>
    </section>
@endif
