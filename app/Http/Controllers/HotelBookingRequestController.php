<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\HotelBookingRequest;
use App\Services\SiteContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HotelBookingRequestController extends Controller
{
    public function store(Request $request, Hotel $hotel): RedirectResponse
    {
        abort_unless($hotel->is_active, 404);

        $requiresHospital = $hotel->hospitals()->exists();
        $requiresRoom = $hotel->rooms()->where('is_active', true)->exists();

        $data = $request->validate([
            'hospital_id' => [Rule::requiredIf($requiresHospital), 'nullable', 'integer', 'exists:hospitals,id'],
            'hotel_room_id' => [Rule::requiredIf($requiresRoom), 'nullable', 'integer', 'exists:hotel_rooms,id'],
            'room_count' => ['required', 'integer', 'min:1', 'max:20'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:50'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'additional_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! empty($data['hospital_id']) && ! $hotel->hospitals()->whereKey($data['hospital_id'])->exists()) {
            throw ValidationException::withMessages([
                'hospital_id' => __('site.hotels.invalid_hospital'),
            ]);
        }

        if (! empty($data['hotel_room_id']) && ! $hotel->rooms()->whereKey($data['hotel_room_id'])->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'hotel_room_id' => __('site.hotels.invalid_room'),
            ]);
        }

        $booking = HotelBookingRequest::create([
            ...$data,
            'hotel_id' => $hotel->id,
            'country_id' => $hotel->country_id,
            'city_id' => $hotel->city_id,
            'ref_number' => HotelBookingRequest::nextReference(),
            'status' => 'new',
            'source' => 'website',
        ]);

        $number = app(SiteContactService::class)->primaryWhatsappNumber();
        $room = $booking->room?->name ?? 'To be discussed';
        $hospital = $booking->hospital?->name ?? 'Not selected';
        $message = "Hello Asian Health Connect, my hotel booking reference is {$booking->ref_number}. Hotel: {$hotel->name}. Related hospital: {$hospital}. Room: {$room}. Check-in: {$booking->check_in->format('d M Y')}. Check-out: {$booking->check_out->format('d M Y')}. Rooms: {$booking->room_count}. Guests: {$booking->guest_count}. Name: {$booking->name}. Phone: {$booking->phone}.";

        return redirect('https://wa.me/'.$number.'?text='.rawurlencode($message))
            ->with('ref_number', $booking->ref_number);
    }
}
