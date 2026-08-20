<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\City;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Faq;
use App\Models\HealthPackage;
use App\Models\HeroImage;
use App\Models\Hospital;
use App\Models\Page;
use App\Models\Review;
use App\Models\Service;
use App\Models\Treatment;
use App\Models\VisaDocument;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home()
    {
        return view('pages.home', [
            'featuredHospitals' => Hospital::with('city', 'country')->where('is_featured', true)->take(6)->get(),
            'featuredDoctors' => Doctor::with('hospital', 'department')->where('is_featured', true)->take(6)->get(),
            'services' => Service::take(9)->get(),
            'reviews' => Review::with('hospital')->where('is_published', true)->take(6)->get(),
            'heroImages' => HeroImage::where('is_active', true)->orderBy('sort_order')->orderByDesc('updated_at')->get(),
            'stats' => [
                'hospitals' => Hospital::count(),
                'doctors' => Doctor::count(),
                'patients' => 2500,
            ],
        ]);
    }

    public function doctors(Request $request)
    {
        $doctors = Doctor::query()
            ->with('hospital.city.country', 'department')
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->q.'%'))
            ->when($request->filled('department'), fn ($query) => $query->whereHas('department', fn ($department) => $department->where('slug', $request->department)))
            ->when($request->filled('hospital'), fn ($query) => $query->whereHas('hospital', fn ($hospital) => $hospital->where('slug', $request->hospital)))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('doctors.index', [
            'doctors' => $doctors,
            'departments' => Department::orderBy('name')->get(),
            'hospitals' => Hospital::orderBy('name')->get(),
        ]);
    }

    public function doctorShow(Doctor $doctor)
    {
        return view('doctors.show', [
            'doctor' => $doctor->load('hospital.city.country', 'department'),
        ]);
    }

    public function hospitals(Request $request)
    {
        $hospitals = Hospital::query()
            ->with('city.country')
            ->when($request->filled('city'), fn ($query) => $query->whereHas('city', fn ($city) => $city->where('slug', $request->city)))
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->q.'%'))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->paginate(12)
            ->withQueryString();

        return view('hospitals.index', [
            'hospitals' => $hospitals,
            'cities' => City::with('country')->orderBy('name')->get(),
        ]);
    }

    public function hospitalShow(Hospital $hospital)
    {
        return view('hospitals.show', [
            'hospital' => $hospital->load('city.country', 'doctors.department', 'treatmentCosts.treatment'),
        ]);
    }

    public function treatments()
    {
        return view('treatments.index', [
            'treatments' => Treatment::with('department', 'costs.hospital')->orderBy('name')->paginate(12),
        ]);
    }

    public function treatmentShow(Treatment $treatment)
    {
        return view('treatments.show', [
            'treatment' => $treatment->load('department', 'costs.hospital.city'),
        ]);
    }

    public function healthPackages()
    {
        return view('pages.health-packages', [
            'packages' => HealthPackage::with('hospital.city')->latest()->paginate(12),
        ]);
    }

    public function services()
    {
        return view('services.index', ['services' => Service::orderBy('name')->get()]);
    }

    public function serviceShow(Service $service)
    {
        return view('services.show', compact('service'));
    }

    public function visaSupport()
    {
        return view('pages.visa-support', [
            'visaDocuments' => VisaDocument::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title_bn')
                ->get(),
        ]);
    }

    public function reviews()
    {
        return view('pages.reviews', [
            'reviews' => Review::with('hospital')->where('is_published', true)->latest()->paginate(12),
        ]);
    }

    public function blog()
    {
        return view('blog.index', [
            'posts' => BlogPost::with('category')->whereNotNull('published_at')->latest('published_at')->paginate(9),
        ]);
    }

    public function blogShow(BlogPost $post)
    {
        return view('blog.show', compact('post'));
    }

    public function about()
    {
        return view('pages.static', ['page' => Page::where('slug', 'about')->first()]);
    }

    public function faq()
    {
        return view('pages.faq', ['faqs' => Faq::orderBy('sort_order')->get()]);
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function emi()
    {
        return view('pages.static', ['page' => Page::where('slug', 'emi')->first()]);
    }
}
