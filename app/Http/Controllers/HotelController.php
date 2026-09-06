<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use App\Models\Hospital;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HotelController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'integer', 'exists:countries,id'],
            'city' => ['nullable', 'integer', 'exists:cities,id'],
            'hospital' => ['nullable', 'integer', 'exists:hospitals,id'],
        ]);

        if (! empty($filters['city'])) {
            $cityIsValid = City::query()
                ->whereKey($filters['city'])
                ->when($filters['country'] ?? null, fn ($query, $countryId) => $query->where('country_id', $countryId))
                ->exists();

            if (! $cityIsValid || empty($filters['country'])) {
                throw ValidationException::withMessages(['city' => __('site.hotels.invalid_city')]);
            }
        }

        if (! empty($filters['hospital'])) {
            $hospitalIsValid = Hospital::query()
                ->whereKey($filters['hospital'])
                ->when($filters['country'] ?? null, fn ($query, $countryId) => $query->where('country_id', $countryId))
                ->when($filters['city'] ?? null, fn ($query, $cityId) => $query->where('city_id', $cityId))
                ->exists();

            if (! $hospitalIsValid || empty($filters['city'])) {
                throw ValidationException::withMessages(['hospital' => __('site.hotels.invalid_hospital')]);
            }
        }

        $countries = Country::orderBy('sort_order')->orderBy('name')->get();
        $cities = City::orderBy('name')->get();
        $hospitals = Hospital::orderBy('name')->get();

        $hotels = Hotel::query()
            ->with('country', 'city', 'hospitals')
            ->where('is_active', true)
            ->when($filters['q'] ?? null, fn ($query, $name) => $query->where('name', 'like', '%'.trim($name).'%'))
            ->when($filters['country'] ?? null, fn ($query, $countryId) => $query->where('country_id', $countryId))
            ->when($filters['city'] ?? null, fn ($query, $cityId) => $query->where('city_id', $cityId))
            ->when($filters['hospital'] ?? null, fn ($query, $hospitalId) => $query->whereHas('hospitals', fn ($query) => $query->whereKey($hospitalId)))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('hotels.index', [
            'hotels' => $hotels,
            'countries' => $countries,
            'filterCities' => $cities->map(fn (City $city): array => [
                'id' => $city->id,
                'country_id' => $city->country_id,
                'name' => $city->name,
            ])->values(),
            'filterHospitals' => $hospitals->map(fn (Hospital $hospital): array => [
                'id' => $hospital->id,
                'country_id' => $hospital->country_id,
                'city_id' => $hospital->city_id,
                'name' => $hospital->name,
            ])->values(),
        ]);
    }

    public function show(Hotel $hotel): View
    {
        abort_unless($hotel->is_active, 404);

        return view('hotels.show', [
            'hotel' => $hotel->load([
                'country',
                'city',
                'hospitals',
                'rooms' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name'),
            ]),
        ]);
    }
}
