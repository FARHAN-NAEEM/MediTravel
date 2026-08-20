<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Models\Treatment;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function index()
    {
        return view('pages.compare', [
            'treatments' => Treatment::orderBy('name')->get(),
            'hospitals' => Hospital::orderBy('name')->get(),
            'selected' => collect(),
        ]);
    }

    public function compare(Request $request)
    {
        $data = $request->validate([
            'treatment_id' => ['required', 'exists:treatments,id'],
            'hospital_ids' => ['required', 'array', 'min:2', 'max:4'],
            'hospital_ids.*' => ['exists:hospitals,id'],
        ]);

        $selected = Hospital::with(['city', 'treatmentCosts' => fn ($query) => $query->where('treatment_id', $data['treatment_id'])])
            ->whereIn('id', $data['hospital_ids'])
            ->get();

        return view('pages.compare', [
            'treatments' => Treatment::orderBy('name')->get(),
            'hospitals' => Hospital::orderBy('name')->get(),
            'selected' => $selected,
            'selectedTreatment' => $data['treatment_id'],
            'selectedHospitals' => $data['hospital_ids'],
        ]);
    }
}
