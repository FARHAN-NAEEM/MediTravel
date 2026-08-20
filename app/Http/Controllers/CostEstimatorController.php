<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Models\Treatment;
use App\Models\TreatmentCost;
use Illuminate\Http\Request;

class CostEstimatorController extends Controller
{
    public function index()
    {
        return view('pages.cost-estimator', [
            'treatments' => Treatment::orderBy('name')->get(),
            'hospitals' => Hospital::orderBy('name')->get(),
            'results' => collect(),
        ]);
    }

    public function estimate(Request $request)
    {
        $data = $request->validate([
            'treatment_id' => ['required', 'exists:treatments,id'],
            'hospital_id' => ['nullable', 'exists:hospitals,id'],
        ]);

        $results = TreatmentCost::with('treatment', 'hospital.city')
            ->where('treatment_id', $data['treatment_id'])
            ->when($data['hospital_id'] ?? null, fn ($query, $hospitalId) => $query->where('hospital_id', $hospitalId))
            ->orderBy('cost_min')
            ->get();

        return view('pages.cost-estimator', [
            'treatments' => Treatment::orderBy('name')->get(),
            'hospitals' => Hospital::orderBy('name')->get(),
            'results' => $results,
            'selectedTreatment' => $data['treatment_id'],
            'selectedHospital' => $data['hospital_id'] ?? null,
        ]);
    }
}
