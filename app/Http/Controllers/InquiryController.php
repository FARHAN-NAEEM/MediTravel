<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Services\SiteContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class InquiryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'type' => ['required', 'string', 'max:40'],
            'doctor_id' => ['nullable', 'exists:doctors,id'],
            'hospital_id' => ['nullable', 'exists:hospitals,id'],
            'treatment_id' => ['nullable', 'exists:treatments,id'],
            'message' => ['nullable', 'string', 'max:2000'],
            'source' => ['nullable', 'string', 'max:80'],
        ]);

        $inquiry = Inquiry::create([
            ...Arr::only($data, ['name', 'phone', 'whatsapp', 'type', 'doctor_id', 'hospital_id', 'treatment_id', 'message']),
            'source' => $data['source'] ?? 'website',
            'ref_number' => Inquiry::nextReference(),
            'status' => 'new',
        ]);

        $inquiry->logs()->create([
            'status' => 'new',
            'note' => 'Inquiry captured from website form.',
        ]);

        return redirect($this->whatsappUrl($inquiry))->with('ref_number', $inquiry->ref_number);
    }

    private function whatsappUrl(Inquiry $inquiry): string
    {
        $number = app(SiteContactService::class)->primaryWhatsappNumber();
        $message = "Hello Asian Health Connect, my reference is {$inquiry->ref_number}. I need help with {$inquiry->type}. Name: {$inquiry->name}, Phone: {$inquiry->phone}.";

        return 'https://wa.me/'.$number.'?text='.rawurlencode($message);
    }
}
