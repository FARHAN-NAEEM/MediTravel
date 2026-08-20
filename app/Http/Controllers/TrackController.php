<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function index()
    {
        return view('pages.track');
    }

    public function show(Request $request)
    {
        $data = $request->validate([
            'ref_number' => ['required', 'string', 'max:40'],
        ]);

        return view('pages.track', [
            'inquiry' => Inquiry::with('logs')->where('ref_number', $data['ref_number'])->first(),
            'searched' => true,
        ]);
    }
}
