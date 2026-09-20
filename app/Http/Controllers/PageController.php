<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\City;
use App\Models\Country;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Faq;
use App\Models\HealthPackage;
use App\Models\HeroImage;
use App\Models\Hospital;
use App\Models\HospitalGroup;
use App\Models\Page;
use App\Models\Review;
use App\Models\Service;
use App\Models\Treatment;
use App\Models\VisaDocument;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function home()
    {
        $destinationCountryNames = [
            'thailand' => 'Thailand',
            'china' => 'China',
            'india' => 'India',
            'singapore' => 'Singapore',
            'malaysia' => 'Malaysia',
        ];

        $destinationCountries = Country::with([
            'hospitals' => fn ($query) => $query
                ->with('city')
                ->orderBy('sort_order')
                ->orderBy('id'),
        ])->get();

        $partnerMarkets = collect($destinationCountryNames)->map(
            fn (string $name, string $key) => [
                'key' => $key,
                'country' => $destinationCountries->first(
                    fn (Country $country) => strcasecmp(trim($country->name), $name) === 0
                ),
            ]
        )->values();

        return view('pages.home', [
            'featuredHospitals' => Hospital::with('city', 'country', 'group')
                ->where(fn ($query) => $query
                    ->where('is_featured', true)
                    ->orWhereIn('slug', [
                        'apollo-hospitals-delhi-delhi',
                        'apollo-health-city-jubilee-hills-hyderabad-hyderabad',
                    ]))
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->take(4)
                ->get(),
            'featuredDoctors' => Doctor::with('hospital', 'department')->where('is_featured', true)->take(6)->get(),
            'partnerMarkets' => $partnerMarkets,
            'services' => Service::take(9)->get(),
            'reviews' => Review::with('hospital')->where('is_published', true)->take(6)->get(),
            'heroImages' => HeroImage::where('is_active', true)->orderBy('sort_order')->orderByDesc('updated_at')->get(),
            'heroSlideInterval' => HeroImage::slideInterval(),
            'stats' => [
                'hospitals' => Hospital::count(),
                'doctors' => Doctor::count(),
                'destinations' => Country::whereHas('hospitals')->count(),
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
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'group' => ['nullable', 'string', 'max:255'],
            'care' => ['nullable', 'string', Rule::in(Hospital::CARE_TYPES)],
        ]);
        $search = trim($filters['q'] ?? '');
        $hospitals = Hospital::query()
            ->with('city', 'country', 'group')
            ->when($filters['country'] ?? '', fn ($query, $country) => $query->whereHas('country', fn ($query) => $query->where('slug', $country)))
            ->when($filters['city'] ?? '', fn ($query, $city) => $query->whereHas('city', fn ($query) => $query->where('slug', $city)))
            ->when($filters['group'] ?? '', fn ($query, $group) => $query->whereHas('group', fn ($query) => $query->where('slug', $group)))
            ->when($filters['care'] ?? '', fn ($query, $care) => $query->where('care_type', $care))
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', '%'.$search.'%')->orWhere('name_bn', 'like', '%'.$search.'%')
                ->orWhereHas('group', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
                ->orWhereHas('city', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return view('hospitals.index', [
            'hospitals' => $hospitals,
            'cities' => City::whereHas('hospitals')->with('country')->orderBy('name')->get(),
            'countries' => Country::whereHas('hospitals')->orderBy('sort_order')->orderBy('name')->get(),
            'groups' => HospitalGroup::whereHas('hospitals')->withCount('hospitals')->orderBy('sort_order')->orderBy('name')->get(),
            'filters' => $filters,
            'search' => $search,
        ]);
    }

    public function hospitalShow(Hospital $hospital)
    {
        return view('hospitals.show', [
            'hospital' => $hospital->load('city', 'country', 'group', 'doctors.department', 'treatmentCosts.treatment'),
        ]);
    }

    public function treatments(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);
        $search = trim($filters['q'] ?? '');
        $department = $filters['department'] ?? '';

        return view('treatments.index', [
            'treatments' => Treatment::with('department')
                ->when($search !== '', fn ($query) => $query->search($search))
                ->when($department !== '', fn ($query) => $query->whereHas('department', fn ($query) => $query->where('slug', $department)))
                ->orderBy('sort_order')->orderBy('name')->paginate(24)->withQueryString(),
            'departments' => Department::whereHas('treatments')->withCount('treatments')->orderBy('name')->get(),
            'catalogCount' => Treatment::count(),
            'search' => $search,
            'selectedDepartment' => $department,
        ]);
    }

    public function treatmentShow(Treatment $treatment)
    {
        return view('treatments.show', [
            'treatment' => $treatment->load('department', 'costs.hospital.city'),
            'relatedTreatments' => Treatment::with('department')->where('department_id', $treatment->department_id)
                ->whereKeyNot($treatment->id)->orderBy('sort_order')->limit(4)->get(),
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
        return view('services.show', [
            'service' => $service->load('requiredDocuments'),
        ]);
    }

    public function visaSupport()
    {
        return view('pages.visa-support', [
            'visaDocuments' => VisaDocument::where('is_active', true)
                ->where('is_required', true)
                ->whereNull('service_id')
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
