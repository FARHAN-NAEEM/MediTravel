<x-layouts.app :title="$hotel->name . ' - Asian Health Connect'" :description="$hotel->description">
    @php($images = $hotel->imageUrls())

    <section class="border-b border-slate-200 bg-slate-50">
        <div class="container-page py-10 md:py-14">
            <a href="{{ route('hotels.index') }}" class="text-sm font-bold text-tealTrust hover:underline">{{ __('site.hotels.back_to_list') }}</a>
            <div class="mt-5 grid items-start gap-8 lg:grid-cols-[1.15fr_0.85fr]">
                <div>
                    <p class="text-sm font-semibold text-tealTrust">{{ $hotel->city?->name }}, {{ $hotel->country?->name }}</p>
                    <h1 class="mt-2 text-3xl font-bold text-navyDeep md:text-5xl">{{ $hotel->name }}</h1>
                    <p class="mt-4 max-w-3xl leading-7 text-slate-600">{{ $hotel->address }}</p>
                    @if ($hotel->area)
                        <p class="mt-2 text-sm font-semibold text-slate-500">{{ $hotel->area }}</p>
                    @endif
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="#booking" class="btn-primary">{{ __('site.hotels.request_booking') }}</a>
                        <a href="#rooms" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-white">{{ __('site.hotels.rooms') }}</a>
                    </div>
                </div>

                @if ($hotel->estimatedCostLabel())
                    <aside class="border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-bold text-tealTrust">{{ __('site.hotels.estimated') }}</p>
                        <p class="mt-2 text-2xl font-bold text-navyDeep">{{ $hotel->estimatedCostLabel() }}</p>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ __('site.hotels.estimated_note') }}</p>
                    </aside>
                @endif
            </div>
        </div>
    </section>

    @if ($images !== [])
        <section class="container-page py-10">
            <h2 class="text-2xl font-bold text-navyDeep">{{ __('site.hotels.gallery') }}</h2>
            <div class="mt-5 grid gap-3 md:grid-cols-2">
                <img src="{{ $images[0] }}" alt="{{ $hotel->name }}" class="aspect-[16/10] h-full w-full rounded-lg object-cover md:row-span-2">
                @foreach (array_slice($images, 1, 2) as $index => $image)
                    <img src="{{ $image }}" alt="{{ $hotel->name }} {{ $index + 2 }}" class="aspect-[16/5] h-full w-full rounded-lg object-cover" loading="lazy">
                @endforeach
            </div>
        </section>
    @endif

    <section class="container-page grid gap-10 py-10 lg:grid-cols-[1fr_0.75fr]">
        <div>
            @if ($hotel->description)
                <div>
                    <h2 class="text-2xl font-bold text-navyDeep">{{ __('site.hotels.description') }}</h2>
                    <p class="mt-4 whitespace-pre-line leading-8 text-slate-600">{{ $hotel->description }}</p>
                </div>
            @endif

            <div class="mt-10">
                <h2 class="text-2xl font-bold text-navyDeep">{{ __('site.hotels.nearby_hospitals') }}</h2>
                @if ($hotel->hospitals->isNotEmpty())
                    <div class="mt-4 divide-y divide-slate-200 border-y border-slate-200">
                        @foreach ($hotel->hospitals as $hospital)
                            <a href="{{ route('hospitals.show', $hospital) }}" class="flex items-center justify-between gap-4 py-4 font-bold text-navyDeep hover:text-tealTrust">
                                <span>{{ $hospital->name }}</span>
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                        @endforeach
                    </div>
                @endif
                @if ($hotel->hospital_distance_note)
                    <p class="mt-4 leading-7 text-slate-600">{{ $hotel->hospital_distance_note }}</p>
                @endif
            </div>
        </div>

        @if ($hotel->special_notes)
            <aside class="border-l-4 border-orangeAction bg-orange-50 p-6">
                <h2 class="text-xl font-bold text-navyDeep">{{ __('site.hotels.special_notes') }}</h2>
                <p class="mt-3 whitespace-pre-line leading-7 text-slate-700">{{ $hotel->special_notes }}</p>
            </aside>
        @endif
    </section>

    <section id="rooms" class="border-y border-slate-200 bg-slate-50">
        <div class="container-page py-10 md:py-14">
            <h2 class="text-2xl font-bold text-navyDeep md:text-3xl">{{ __('site.hotels.rooms') }}</h2>
            @if ($hotel->rooms->isEmpty())
                <p class="mt-4 max-w-3xl leading-7 text-slate-600">{{ __('site.hotels.no_rooms') }}</p>
            @else
                <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($hotel->rooms as $room)
                        <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="text-xl font-bold text-navyDeep">{{ $room->name }}</h3>
                            <dl class="mt-4 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                                <dt class="text-slate-500">{{ __('site.hotels.beds') }}</dt>
                                <dd class="font-bold text-slate-800">{{ $room->bed_count }}</dd>
                                @if ($room->room_size)
                                    <dt class="text-slate-500">{{ __('site.hotels.room_size') }}</dt>
                                    <dd class="font-bold text-slate-800">{{ $room->room_size }}</dd>
                                @endif
                                @if ($room->bathroom)
                                    <dt class="text-slate-500">{{ __('site.hotels.bathroom') }}</dt>
                                    <dd class="font-bold text-slate-800">{{ $room->bathroom }}</dd>
                                @endif
                                <dt class="text-slate-500">{{ __('site.hotels.ac') }}</dt>
                                <dd class="font-bold text-slate-800">{{ $room->has_ac ? __('site.hotels.yes') : __('site.hotels.no') }}</dd>
                                <dt class="text-slate-500">{{ __('site.hotels.wifi') }}</dt>
                                <dd class="font-bold text-slate-800">{{ $room->has_wifi ? __('site.hotels.yes') : __('site.hotels.no') }}</dd>
                            </dl>
                            @if ($room->facilities)
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($room->facilities as $facility)
                                        <span class="rounded-md bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $facility }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if ($room->description)
                                <p class="mt-4 text-sm leading-6 text-slate-600">{{ $room->description }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section id="booking" class="container-page py-10 md:py-14">
        <div class="max-w-4xl">
            <p class="text-sm font-bold text-tealTrust">Asian Health Connect</p>
            <h2 class="mt-2 text-3xl font-bold text-navyDeep">{{ __('site.hotels.booking_title') }}</h2>
            <p class="mt-3 leading-7 text-slate-600">{{ __('site.hotels.booking_subtitle') }}</p>

            @if ($errors->any())
                <div class="mt-6 border-l-4 border-red-500 bg-red-50 p-4 text-sm text-red-700">
                    <ul class="grid gap-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('hotels.booking-requests.store', $hotel) }}" method="POST" class="mt-7 grid gap-5 border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-2 md:p-7">
                @csrf
                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    {{ __('site.hotels.hospital') }}@if ($hotel->hospitals->isNotEmpty())<span class="text-red-600">*</span>@endif
                    <select name="hospital_id" @required($hotel->hospitals->isNotEmpty()) class="rounded-md border-slate-200">
                        <option value="">{{ __('site.hotels.select_hospital') }}</option>
                        @foreach ($hotel->hospitals as $hospital)
                            <option value="{{ $hospital->id }}" @selected(old('hospital_id') == $hospital->id)>{{ $hospital->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    {{ __('site.hotels.room_type') }}@if ($hotel->rooms->isNotEmpty())<span class="text-red-600">*</span>@endif
                    <select name="hotel_room_id" @required($hotel->rooms->isNotEmpty()) class="rounded-md border-slate-200">
                        <option value="">{{ __('site.hotels.select_room') }}</option>
                        @foreach ($hotel->rooms as $room)
                            <option value="{{ $room->id }}" @selected(old('hotel_room_id') == $room->id)>{{ $room->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    {{ __('site.hotels.check_in') }}<span class="text-red-600">*</span>
                    <input type="date" name="check_in" value="{{ old('check_in') }}" min="{{ now()->toDateString() }}" required class="rounded-md border-slate-200">
                </label>

                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    {{ __('site.hotels.check_out') }}<span class="text-red-600">*</span>
                    <input type="date" name="check_out" value="{{ old('check_out') }}" min="{{ now()->addDay()->toDateString() }}" required class="rounded-md border-slate-200">
                </label>

                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    {{ __('site.hotels.room_count') }}<span class="text-red-600">*</span>
                    <input type="number" name="room_count" value="{{ old('room_count', 1) }}" min="1" max="20" required class="rounded-md border-slate-200">
                </label>

                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    {{ __('site.hotels.guest_count') }}<span class="text-red-600">*</span>
                    <input type="number" name="guest_count" value="{{ old('guest_count', 1) }}" min="1" max="50" required class="rounded-md border-slate-200">
                </label>

                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    {{ __('site.hotels.patient_name') }}<span class="text-red-600">*</span>
                    <input name="name" value="{{ old('name') }}" maxlength="120" required class="rounded-md border-slate-200">
                </label>

                <label class="grid gap-2 text-sm font-semibold text-slate-700">
                    {{ __('site.hotels.phone') }}<span class="text-red-600">*</span>
                    <input type="tel" name="phone" value="{{ old('phone') }}" maxlength="40" required class="rounded-md border-slate-200">
                </label>

                <label class="grid gap-2 text-sm font-semibold text-slate-700 md:col-span-2">
                    {{ __('site.hotels.whatsapp') }}
                    <input type="tel" name="whatsapp" value="{{ old('whatsapp') }}" maxlength="40" class="rounded-md border-slate-200">
                </label>

                <label class="grid gap-2 text-sm font-semibold text-slate-700 md:col-span-2">
                    {{ __('site.hotels.additional_note') }}
                    <textarea name="additional_note" rows="4" maxlength="2000" class="rounded-md border-slate-200">{{ old('additional_note') }}</textarea>
                </label>

                <div class="md:col-span-2">
                    <button class="btn-primary">{{ __('site.hotels.submit_booking') }}</button>
                </div>
            </form>
        </div>
    </section>
</x-layouts.app>
