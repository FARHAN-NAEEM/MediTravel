<x-layouts.app :title="__('treatments.title') . ' - Asian Health Connect'" :description="__('treatments.intro')">
    <section class="treatment-catalog__intro">
        <div class="container-page">
            <nav class="mb-6 text-sm text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-tealTrust">{{ __('site.nav.home') }}</a>
                <span class="mx-2" aria-hidden="true">/</span>
                <span aria-current="page">{{ __('site.pages.treatments') }}</span>
            </nav>
            <div class="treatment-catalog__heading">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold text-tealTrust">{{ __('treatments.eyebrow') }}</p>
                    <h1 class="mt-2 text-3xl font-bold leading-snug sm:text-4xl">{{ __('treatments.title') }}</h1>
                    <p class="mt-4 text-base leading-7 text-slate-600">{{ __('treatments.intro') }}</p>
                </div>
                <dl class="treatment-catalog__counts">
                    <div><dd>{{ $catalogCount }}</dd><dt>{{ __('treatments.topics') }}</dt></div>
                    <div><dd>{{ $departments->count() }}</dd><dt>{{ __('treatments.specialties') }}</dt></div>
                </dl>
            </div>
            <form method="GET" action="{{ route('treatments.index') }}" class="treatment-catalog__filters" role="search">
                <div>
                    <label for="treatment-search">{{ __('treatments.search_label') }}</label>
                    <div class="relative">
                        <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-3.5 h-5 w-5 text-slate-400" aria-hidden="true" />
                        <input id="treatment-search" type="search" name="q" value="{{ $search }}" maxlength="120" placeholder="{{ __('treatments.search_placeholder') }}" class="w-full rounded-md border-slate-300 py-3 pl-10 pr-3 focus:border-tealTrust focus:ring-tealTrust">
                    </div>
                </div>
                <div>
                    <label for="treatment-department">{{ __('treatments.department_label') }}</label>
                    <select id="treatment-department" name="department" class="w-full rounded-md border-slate-300 py-3 focus:border-tealTrust focus:ring-tealTrust">
                        <option value="">{{ __('treatments.all_departments') }}</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->slug }}" @selected($selectedDepartment === $department->slug)>{{ $department->localized('name') }} ({{ $department->treatments_count }})</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn-primary min-h-12 gap-2">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5" aria-hidden="true" />{{ __('treatments.search') }}
                </button>
            </form>
        </div>
    </section>
    <section class="container-page pb-14 pt-7" aria-label="{{ __('treatments.title') }}">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <p class="font-semibold text-slate-700" aria-live="polite">{{ __('treatments.results', ['count' => $treatments->total()]) }}</p>
            @if ($search !== '' || $selectedDepartment !== '')
                <a href="{{ route('treatments.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-tealTrust underline underline-offset-4">
                    <x-heroicon-o-x-mark class="h-4 w-4" aria-hidden="true" />{{ __('treatments.clear') }}
                </a>
            @else
                <p class="text-sm text-slate-500">{{ __('treatments.showing', ['from' => $treatments->firstItem() ?? 0, 'to' => $treatments->lastItem() ?? 0, 'total' => $treatments->total()]) }}</p>
            @endif
        </div>
        @if ($treatments->isNotEmpty())
            <div class="treatment-grid">
                @foreach ($treatments as $treatment)
                    <x-treatment-card :treatment="$treatment" />
                @endforeach
            </div>
            <div class="mt-9">{{ $treatments->links() }}</div>
        @else
            <div class="border-y border-slate-200 py-14 text-center">
                <x-heroicon-o-magnifying-glass class="mx-auto h-9 w-9 text-tealTrust" aria-hidden="true" />
                <h2 class="mt-4 text-xl font-bold">{{ __('treatments.empty') }}</h2>
                <p class="mx-auto mt-3 max-w-xl leading-7 text-slate-600">{{ __('treatments.empty_body') }}</p>
                <a href="{{ route('contact') }}" class="btn-primary mt-6">{{ __('treatments.contact') }}</a>
            </div>
        @endif
        <p class="mt-9 border-t border-slate-200 pt-5 text-sm leading-7 text-slate-500">{{ __('treatments.medical_note') }}</p>
    </section>
</x-layouts.app>
