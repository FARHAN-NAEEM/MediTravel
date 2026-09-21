<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Models\HospitalCommissionAgreement;
use App\Models\ReferralAgent;
use App\Models\ReferralAudit;
use App\Models\ReferralCase;
use App\Models\ReferralCommissionRule;
use App\Models\ReferralLedgerEntry;
use App\Models\Treatment;
use App\Models\User;
use App\Services\ReferralNetwork;
use App\Support\AdminAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

class ReferralOperationsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['status' => ['nullable', 'string', Rule::in(array_keys(ReferralCase::statuses()))], 'q' => 'nullable|string|max:60', 'due' => 'nullable|boolean']);
        $query = ReferralCase::with('agent', 'patient', 'hospital', 'assignee');
        if (! $request->user()->isOwner()) {
            $query->where('assigned_to', $request->user()->id);
        }
        $base = clone $query;
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('q')) {
            $query->where('reference', 'like', '%'.$request->string('q').'%');
        }
        if ($request->boolean('due')) {
            $query->whereDate('follow_up_on', '<=', today())->whereNotIn('status', ['completed', 'cancelled', 'duplicate']);
        }
        $counts = ['new' => (clone $base)->where('status', 'new')->count(), 'due' => (clone $base)->whereDate('follow_up_on', '<=', today())->whereNotIn('status', ['completed', 'cancelled', 'duplicate'])->count(), 'completed' => (clone $base)->where('status', 'completed')->count()];

        return view('referrals.operations', ['cases' => $query->latest('id')->paginate(20)->withQueryString(), 'counts' => $counts]);
    }

    public function agents(Request $request, ReferralNetwork $network)
    {
        $network->owner($request->user());

        return view('referrals.agents', ['agents' => ReferralAgent::withCount('cases')->orderBy('name')->paginate(20), 'editing' => $request->filled('edit') ? ReferralAgent::findOrFail($request->integer('edit')) : null]);
    }

    public function saveAgent(Request $request, ReferralNetwork $network)
    {
        $network->saveAgent($request->user(), $request->all(), $request->filled('agent_id') ? ReferralAgent::findOrFail($request->integer('agent_id')) : null);

        return redirect()->route('referral-ops.agents')->with('success', 'এজেন্টের তথ্য সংরক্ষিত হয়েছে।');
    }

    public function invite(Request $request, ReferralAgent $agent, ReferralNetwork $network)
    {
        $network->owner($request->user());
        abort_unless($agent->is_active, 422);
        try {
            $status = Password::broker('agents')->sendResetLink(['email' => $agent->email, 'is_active' => true]);
        } catch (\Throwable $e) {
            logger()->warning('Agent invitation email failed.', ['exception_type' => $e::class]);

            return back()->withErrors(['email' => 'ইমেইল পাঠানো যায়নি। SMTP পরীক্ষা করে আবার চেষ্টা করুন।']);
        }
        $network->audit($request->user(), 'invitation_requested', 'Password invitation requested.', agent: $agent);

        return back()->with('success', $status === Password::RESET_LINK_SENT ? 'একবার ব্যবহারযোগ্য পাসওয়ার্ড লিংক পাঠানো হয়েছে।' : 'কিছুক্ষণ পরে আবার অনুরোধ করুন।');
    }

    public function rules(Request $request, ReferralNetwork $network)
    {
        $network->owner($request->user());

        return view('referrals.rules', $this->options() + ['agents' => ReferralAgent::orderBy('name')->get(),
            'rules' => ReferralCommissionRule::with('agent', 'hospital', 'treatment')->latest()->paginate(20, ['*'], 'rules_page'),
            'agreements' => HospitalCommissionAgreement::with('hospital', 'treatment')->latest()->paginate(10, ['*'], 'agreements_page')]);
    }

    public function rule(Request $request, ReferralNetwork $network)
    {
        $network->createRule($request->user(), $request->all());

        return back()->with('success', 'নতুন কমিশন নিয়ম সংরক্ষিত। গৃহীত কেসের হার অপরিবর্তিত আছে।');
    }

    public function disableRule(Request $request, ReferralCommissionRule $rule, ReferralNetwork $network)
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $network->disableRule($request->user(), $rule, $request->input('reason'));

        return back()->with('success', 'নিয়ম বন্ধ করা হয়েছে।');
    }

    public function agreement(Request $request, ReferralNetwork $network)
    {
        $network->createAgreement($request->user(), $request->all());

        return back()->with('success', 'হাসপাতালের চুক্তির নতুন সংস্করণ সংরক্ষিত হয়েছে।');
    }

    public function show(Request $request, ReferralCase $case, ReferralNetwork $network)
    {
        $network->access($request->user(), $case);
        $case->load('agent', 'patient', 'hospital', 'treatment', 'assignee');
        $owner = $request->user()->isOwner();

        return view('referrals.case', $this->options() + ['case' => $case, 'owner' => $owner,
            'staff' => $owner ? User::permission(AdminAccess::PANEL_PERMISSION)->where('is_active', true)->get(['id', 'name']) : collect(),
            'agents' => $owner ? ReferralAgent::where('is_active', true)->get(['id', 'name', 'business_name']) : collect(),
            'duplicates' => $owner ? $network->duplicates($case)->with('patient')->get() : collect(),
            'hasDuplicates' => $network->duplicates($case)->exists(),
            'balance' => $owner ? $network->balance($case) : null,
            'entries' => $owner ? $case->entries()->with('reversal')->latest()->get() : collect(),
            'audits' => $owner ? $case->audits()->with('actor')->get() : $case->audits()->whereIn('action', ['submitted', 'progress_updated'])->with('actor')->get()]);
    }

    public function progress(Request $request, ReferralCase $case, ReferralNetwork $network)
    {
        $network->progress($request->user(), $case, $request->all());

        return back()->with('success', 'কেসের অগ্রগতি সংরক্ষিত হয়েছে।');
    }

    public function finance(Request $request, ReferralCase $case, ReferralNetwork $network)
    {
        $network->owner($request->user());
        $action = $request->validate(['action' => 'required|in:override,acknowledge,approve,entry,reverse'])['action'];
        $request->validate(['reason' => 'required_unless:action,entry|string|max:2000']);
        match ($action) {
            'override' => $network->overrideRate($request->user(), $case, $request->validate(['percentage' => 'required|string'])['percentage'], $request->input('reason')),
            'acknowledge' => $network->acknowledge($request->user(), $case, (int) $request->validate(['rate_version' => 'required|integer'])['rate_version'], $request->input('reason')),
            'approve' => $network->approve($request->user(), $case, $request->input('reason')),
            'entry' => $network->entry($request->user(), $case, $request->all()),
            'reverse' => $network->reverse($request->user(), $case, ReferralLedgerEntry::findOrFail($request->validate(['entry_id' => 'required|integer'])['entry_id']), $request->input('reason')),
        };

        return back()->with('success', 'কমিশনের হিসাব সংরক্ষিত হয়েছে।');
    }

    public function manual(Request $request, ReferralNetwork $network)
    {
        $network->owner($request->user());

        return view('referrals.manual', $this->options() + ['agents' => ReferralAgent::where('is_active', true)->orderBy('name')->get()]);
    }

    public function storeManual(Request $request, ReferralNetwork $network)
    {
        $network->owner($request->user());
        $request->validate(['agent_id' => 'required|exists:referral_agents,id']);
        $case = $network->intake(ReferralAgent::findOrFail($request->integer('agent_id')), $request->all(), 'staff', $request->user());

        return redirect()->route('referral-ops.show', $case);
    }

    public function audit(Request $request, ReferralNetwork $network)
    {
        $network->owner($request->user());

        return view('referrals.audit', ['audits' => ReferralAudit::with('actor')->latest('id')->paginate(40)]);
    }

    private function options(): array
    {
        return ['hospitals' => Hospital::orderBy('name')->get(['id', 'name']), 'treatments' => Treatment::orderBy('name')->get(['id', 'name'])];
    }
}
