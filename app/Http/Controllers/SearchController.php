<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Hotel;
use App\Models\Treatment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $query = trim($validated['q']);

        if ($hospital = Hospital::where('name', $query)->first()) {
            return redirect()->route('hospitals.show', $hospital);
        }

        if ($hotel = Hotel::where('is_active', true)->where('name', $query)->first()) {
            return redirect()->route('hotels.show', $hotel);
        }

        if ($treatment = Treatment::where('name', $query)->first()) {
            return redirect()->route('treatments.show', $treatment);
        }

        if ($doctor = Doctor::where('name', $query)->first()) {
            return redirect()->route('doctors.show', $doctor);
        }

        $likeQuery = '%'.$query.'%';

        return view('pages.search', [
            'query' => $query,
            'hospitals' => Hospital::with('city.country')
                ->where('name', 'like', $likeQuery)
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->limit(9)
                ->get(),
            'hotels' => Hotel::with('city.country', 'hospitals')
                ->where('is_active', true)
                ->where('name', 'like', $likeQuery)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(9)
                ->get(),
            'doctors' => Doctor::with('hospital', 'department')
                ->where('name', 'like', $likeQuery)
                ->orderByDesc('is_featured')
                ->limit(9)
                ->get(),
            'treatments' => Treatment::with('department')
                ->where('name', 'like', $likeQuery)
                ->orderByDesc('is_featured')
                ->orderBy('name')
                ->limit(9)
                ->get(),
        ]);
    }
}
