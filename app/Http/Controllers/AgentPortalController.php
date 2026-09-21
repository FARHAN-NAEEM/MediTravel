<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Models\ReferralAgent;
use App\Models\ReferralCase;
use App\Models\Treatment;
use App\Services\ReferralNetwork;
use App\Services\SiteContactService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AgentPortalController extends Controller
{
    public function dashboard(Request $request, ReferralNetwork $network)
    {
        $request->validate(['status' => ['nullable', 'string', Rule::in(array_keys(ReferralCase::statuses()))]]);
        $agent = Auth::guard('agent')->user();
        $query = $agent->cases();
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        $cases = $query->with('patient', 'hospital', 'treatment')->latest('id')->paginate(15)->withQueryString();
        $balances = $cases->getCollection()->mapWithKeys(fn ($case) => [$case->id => $network->balance($case)]);
        $counts = ['submitted' => $agent->cases()->count(), 'verified' => $agent->cases()->whereNotNull('verified_at')->where('status', '!=', 'duplicate')->count(), 'completed' => $agent->cases()->where('status', 'completed')->count()];

        return view('referrals.agent-dashboard', compact('agent', 'cases', 'balances', 'counts'));
    }

    public function create()
    {
        return $this->form(Auth::guard('agent')->user(), false);
    }

    public function show(string $reference, ReferralNetwork $network)
    {
        $case = Auth::guard('agent')->user()->cases()->where('reference', $reference)->with('patient', 'hospital')->firstOrFail();

        return view('referrals.agent-case', ['case' => $case, 'balance' => $network->balance($case),
            'payments' => $case->entries()->whereIn('type', ['payout', 'payout_reversal'])->latest()->get()]);
    }

    public function store(Request $request, ReferralNetwork $network)
    {
        $network->intake(Auth::guard('agent')->user(), $request->all(), 'agent');

        return redirect()->route('agent.dashboard')->with('success', 'রেফারেল জমা হয়েছে।');
    }

    public function publicForm(string $code)
    {
        return $this->form(ReferralAgent::where('code', $code)->where('is_active', true)->firstOrFail(), true);
    }

    private function form(ReferralAgent $agent, bool $public)
    {
        return view('referrals.intake', ['agent' => $agent, 'public' => $public, 'submissionKey' => (string) Str::uuid(),
            'hospitals' => Hospital::orderBy('name')->get(['id', 'name']), 'treatments' => Treatment::orderBy('name')->get(['id', 'name'])]);
    }

    public function publicStore(Request $request, string $code, ReferralNetwork $network)
    {
        $case = $network->intake(ReferralAgent::where('code', $code)->where('is_active', true)->firstOrFail(), $request->all(), 'patient');
        $message = 'Hello Asian Health Connect, my referral reference is '.$case->reference.'.';

        return redirect()->route('referral.public', $code)->with('referral_saved', $case->reference)
            ->with('referral_whatsapp', 'https://wa.me/'.app(SiteContactService::class)->primaryWhatsappNumber().'?text='.rawurlencode($message));
    }

    public function acknowledge(Request $request, string $reference, ReferralNetwork $network)
    {
        $request->validate(['rate_version' => 'required|integer', 'agree' => 'accepted']);
        $agent = Auth::guard('agent')->user();
        $case = $agent->cases()->where('reference', $reference)->firstOrFail();
        $network->acknowledge($agent, $case, $request->integer('rate_version'));

        return back()->with('success', 'কেসের কমিশনের হার গ্রহণ করা হয়েছে।');
    }

    public function statement(ReferralNetwork $network)
    {
        $agent = Auth::guard('agent')->user();

        return response()->streamDownload(function () use ($agent, $network) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Case reference', 'Status', 'Percentage', 'Calculated BDT', 'Approved BDT', 'Paid BDT', 'Payable BDT']);
            foreach ($agent->cases()->cursor() as $case) {
                $b = $network->balance($case);
                fputcsv($out, [$case->reference, $case->status, $case->commission_rate_bps === null ? '' : $case->commission_rate_bps / 100,
                    $b['calculated'] / 100, $b['approved'] / 100, $b['paid'] / 100, $b['payable'] / 100]);
            }
            fclose($out);
        }, 'agent-statement.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
