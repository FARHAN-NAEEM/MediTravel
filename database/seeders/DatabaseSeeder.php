<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\City;
use App\Models\Country;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Faq;
use App\Models\HealthPackage;
use App\Models\Hospital;
use App\Models\Page;
use App\Models\Review;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Treatment;
use App\Models\TreatmentCost;
use App\Models\User;
use App\Models\VisaDocument;
use App\Support\AdminAccess;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $ownerEmail = config('admin.owner_email');
        $admin = User::withTrashed()->where('is_owner', true)->first()
            ?? User::withTrashed()->where('email', $ownerEmail)->first();

        if (! $admin) {
            $configuredPassword = config('admin.owner_password');
            $admin = User::query()->create([
                'name' => config('admin.owner_name'),
                'email' => $ownerEmail,
                'password' => Hash::make($configuredPassword ?: Str::password(48)),
            ]);
        }

        DB::table('users')->where('id', $admin->id)->update([
            'is_owner' => true,
            'is_active' => true,
            'deleted_at' => null,
        ]);
        $admin->refresh();

        if (class_exists(Role::class) && Schema::hasTable('roles')) {
            foreach ([AdminAccess::OWNER_ROLE, AdminAccess::USER_ADMIN_ROLE] as $role) {
                Role::findOrCreate($role);
            }

            $admin->syncRoles([AdminAccess::OWNER_ROLE]);
        }

        $india = Country::updateOrCreate(['slug' => 'india'], ['name' => 'India', 'flag' => 'in', 'sort_order' => 1]);
        $bangladesh = Country::updateOrCreate(['slug' => 'bangladesh'], ['name' => 'Bangladesh', 'flag' => 'bd', 'sort_order' => 2]);

        $chennai = City::updateOrCreate(['slug' => 'chennai'], ['country_id' => $india->id, 'name' => 'Chennai']);
        $kolkata = City::updateOrCreate(['slug' => 'kolkata'], ['country_id' => $india->id, 'name' => 'Kolkata']);
        City::updateOrCreate(['slug' => 'dhaka'], ['country_id' => $bangladesh->id, 'name' => 'Dhaka']);

        $cardiac = Department::updateOrCreate(['slug' => 'cardiac'], ['name' => 'Cardiac Care', 'icon' => 'heart-pulse']);
        $cancer = Department::updateOrCreate(['slug' => 'cancer'], ['name' => 'Cancer Care', 'icon' => 'ribbon']);
        $ortho = Department::updateOrCreate(['slug' => 'orthopedic'], ['name' => 'Orthopedic', 'icon' => 'bone']);
        $neuro = Department::updateOrCreate(['slug' => 'neurology'], ['name' => 'Neurology', 'icon' => 'brain']);

        $apollo = Hospital::updateOrCreate(['slug' => 'apollo-hospitals-chennai'], [
            'country_id' => $india->id,
            'city_id' => $chennai->id,
            'name' => 'Apollo Hospitals Chennai',
            'description' => 'Multi-specialty hospital popular with international patients from Bangladesh.',
            'accreditation' => 'NABH, JCI',
            'is_featured' => true,
            'sort_order' => 1,
        ]);

        $fortis = Hospital::updateOrCreate(['slug' => 'fortis-hospital-kolkata'], [
            'country_id' => $india->id,
            'city_id' => $kolkata->id,
            'name' => 'Fortis Hospital Kolkata',
            'description' => 'Accessible specialty care in Kolkata with Bangla-friendly patient support.',
            'accreditation' => 'NABH',
            'is_featured' => true,
            'sort_order' => 2,
        ]);

        Doctor::updateOrCreate(['slug' => 'dr-arvind-rao'], [
            'hospital_id' => $apollo->id,
            'department_id' => $cardiac->id,
            'name' => 'Dr. Arvind Rao',
            'designation' => 'Senior Consultant - Cardiac Surgery',
            'qualifications' => 'MS, MCh Cardio Thoracic Surgery',
            'experience_years' => 18,
            'bio' => 'Experienced cardiac surgeon for bypass and valve procedures.',
            'is_featured' => true,
        ]);

        Doctor::updateOrCreate(['slug' => 'dr-sneha-mukherjee'], [
            'hospital_id' => $fortis->id,
            'department_id' => $cancer->id,
            'name' => 'Dr. Sneha Mukherjee',
            'designation' => 'Consultant - Medical Oncology',
            'qualifications' => 'MD, DM Oncology',
            'experience_years' => 12,
            'bio' => 'Focuses on chemo planning and second opinion for oncology patients.',
            'is_featured' => true,
        ]);

        $treatments = [
            ['Bypass Surgery', 'bypass-surgery', $cardiac, 380000, 650000, $apollo],
            ['Cancer Treatment Planning', 'cancer-treatment-planning', $cancer, 90000, 240000, $fortis],
            ['Knee Replacement', 'knee-replacement', $ortho, 250000, 480000, $apollo],
            ['Neuro Consultation', 'neuro-consultation', $neuro, 8000, 25000, $fortis],
        ];

        foreach ($treatments as [$name, $slug, $department, $min, $max, $hospital]) {
            $treatment = Treatment::updateOrCreate(['slug' => $slug], [
                'department_id' => $department->id,
                'name' => $name,
                'description' => 'Estimated range varies by patient condition, investigation, cabin type, and stay duration.',
                'is_featured' => true,
            ]);

            TreatmentCost::updateOrCreate(
                ['treatment_id' => $treatment->id, 'hospital_id' => $hospital->id],
                ['cost_min' => $min, 'cost_max' => $max, 'currency' => 'INR', 'notes' => 'Indicative range only. Final quote requires medical report review.']
            );
        }

        HealthPackage::updateOrCreate(['slug' => 'executive-health-check-apollo'], [
            'hospital_id' => $apollo->id,
            'name' => 'Executive Health Check',
            'price' => 18500,
            'currency' => 'INR',
            'includes' => ['CBC', 'Liver profile', 'ECG', 'Chest X-ray', 'Doctor consultation'],
        ]);

        foreach ([
            'Appointment' => 'doctor-appointment',
            'Second Opinion' => 'second-opinion',
            'Cost Estimate' => 'cost-estimate',
            'Visa Support' => 'visa-support',
            'Flight and Airport Pickup' => 'flight-airport-pickup',
            'Hotel Stay' => 'hotel-stay',
            'Teleconsultation' => 'teleconsultation',
            'Emergency Support' => 'emergency-support',
            'Post-treatment Follow-up' => 'post-treatment-follow-up',
        ] as $name => $slug) {
            Service::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'icon' => 'sparkles',
                'short_desc' => 'Fast WhatsApp-first support handled by the Asian Health Connect team.',
                'body' => 'Submit a short form, receive a reference number, and continue the conversation on WhatsApp with all context preserved in the admin CRM.',
            ]);
        }

        Review::updateOrCreate(['patient_name' => 'Md. Hasan Ali'], [
            'hospital_id' => $apollo->id,
            'rating' => 5,
            'body' => 'Appointment, visa invitation, and airport pickup were coordinated quickly. The reference number helped us track every step.',
            'treatment' => 'Cardiac Care',
            'is_verified' => true,
            'is_published' => true,
        ]);

        $category = BlogCategory::updateOrCreate(['slug' => 'medical-visa'], ['name' => 'Medical Visa']);
        BlogPost::updateOrCreate(['slug' => 'india-medical-visa-checklist-bangladesh'], [
            'blog_category_id' => $category->id,
            'title' => 'India medical visa checklist for Bangladeshi patients',
            'body' => 'Keep passport, recent photo, hospital appointment letter, medical reports, bank statement, and attendant documents ready before applying.',
            'meta_title' => 'India medical visa checklist from Bangladesh',
            'meta_description' => 'Simple checklist for Bangladeshi patients planning treatment in India.',
            'published_at' => now(),
        ]);

        foreach ([
            'How do I get an appointment?' => 'Submit the form or tap WhatsApp. We collect basic information and coordinate with the hospital.',
            'Do you show final treatment cost?' => 'We show indicative ranges. Final cost depends on reports, doctor advice, cabin, and stay duration.',
            'Can I track without login?' => 'Yes. Use your reference number on the tracking page.',
        ] as $question => $answer) {
            Faq::updateOrCreate(['question' => $question], ['answer' => $answer, 'category' => 'general']);
        }

        foreach ([
            ['Valid passport', 'ভ্যালিড পাসপোর্ট', 1],
            ['Recent photo', 'সাম্প্রতিক ছবি', 2],
            ['Hospital appointment or invitation letter', 'হাসপাতালের appointment বা invitation letter', 3],
            ['Medical reports and prescriptions', 'মেডিকেল রিপোর্ট ও prescription', 4],
            ['Bank statement', 'ব্যাংক স্টেটমেন্ট', 5],
            ['National ID or birth certificate', 'জাতীয় পরিচয়পত্র বা জন্ম নিবন্ধন', 6],
            ['Attendant documents', 'Attendant-এর প্রয়োজনীয় documents', 7],
        ] as [$titleEn, $titleBn, $sortOrder]) {
            VisaDocument::updateOrCreate(
                ['title_bn' => $titleBn],
                [
                    'title_en' => $titleEn,
                    'category' => 'medical_visa',
                    'is_required' => true,
                    'is_active' => true,
                    'sort_order' => $sortOrder,
                ]
            );
        }

        Page::updateOrCreate(['slug' => 'about'], [
            'title' => 'About Asian Health Connect',
            'body' => 'Asian Health Connect helps Bangladeshi patients connect with hospitals and doctors abroad while keeping communication simple through WhatsApp and phone support.',
        ]);

        Page::updateOrCreate(['slug' => 'emi'], [
            'title' => 'EMI and installment information',
            'body' => 'Installment or medical finance options depend on partner policy. Contact support for the latest available options.',
        ]);

        foreach ([
            'whatsapp_number' => env('WHATSAPP_NUMBER', '8801700000000'),
            'support_phone' => env('SUPPORT_PHONE', '+8801700000000'),
            'support_email' => env('SUPPORT_EMAIL', 'care@asianhealthconnect.com'),
            'site_tagline' => 'Bangla-first medical travel support for India and abroad.',
        ] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->call(SiteContactSeeder::class);
    }
}
