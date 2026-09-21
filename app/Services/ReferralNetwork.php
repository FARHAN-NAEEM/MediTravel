<?php

namespace App\Services;

use App\Models\HospitalCommissionAgreement;
use App\Models\ReferralAgent;
use App\Models\ReferralAudit;
use App\Models\ReferralCase;
use App\Models\ReferralCommissionRule;
use App\Models\ReferralLedgerEntry;
use App\Models\ReferralPatient;
use App\Models\User;
use App\Support\AdminAccess;
use App\Support\ReferralMoney as Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReferralNetwork
{
    public function owner(User $actor): void
    {
        abort_unless($actor->isOwner() && $actor->is_active && ! $actor->trashed(), 403);
    }

    public function access(User $actor, ReferralCase $case): void
    {
        abort_unless($actor->is_active && ! $actor->trashed() && $actor->can(AdminAccess::PANEL_PERMISSION)
            && ($actor->isOwner() || $case->assigned_to === $actor->id), 403);
    }

    public function audit(?User $actor, string $action, string $note, ?ReferralCase $case = null, ?ReferralAgent $agent = null, array $changes = []): void
    {
        ReferralAudit::create(['created_at' => now(), 'actor_id' => $actor?->id, 'agent_id' => $agent?->id ?? $case?->agent_id,
            'case_id' => $case?->id, 'action' => $action, 'note' => $note, 'changes' => $changes ?: null]);
    }

    public function saveAgent(User $actor, array $data, ?ReferralAgent $agent = null): ReferralAgent
    {
        $this->owner($actor);
        $data['email'] = strtolower(trim($data['email'] ?? ''));
        $values = Validator::make($data, [
            'name' => 'required|string|max:120', 'business_name' => 'required|string|max:180',
            'email' => ['required', 'email', 'max:190', Rule::unique('referral_agents')->ignore($agent?->id)],
            'phone' => 'required|string|max:32', 'district' => 'required|string|max:100',
            'address' => 'nullable|string|max:1000', 'payment_details' => 'nullable|string|max:2000',
            'is_active' => 'required|boolean', 'reason' => 'required|string|max:1000',
        ])->validate();

        return DB::transaction(function () use ($actor, $values, $agent) {
            $agent = $agent ? ReferralAgent::lockForUpdate()->findOrFail($agent->id) : new ReferralAgent;
            $old = $agent->only(['name', 'business_name', 'email', 'is_active']);
            $oldPayment = $agent->payment_details;
            $exists = $agent->exists;
            $agent->fill(collect($values)->except('reason')->all());
            $agent->phone = Money::phone($values['phone']);
            if (! $exists) {
                $agent->code = 'AHC-AG-'.Str::upper(Str::random(10));
                $agent->password = Str::random(64);
            } elseif ($agent->isDirty(['email', 'is_active'])) {
                DB::table('agent_password_reset_tokens')->whereIn('email', array_unique([$agent->getOriginal('email'), $agent->email]))->delete();
                $agent->session_version++;
                $agent->remember_token = null;
            }
            if ($agent->is_active && ! $agent->approved_at) {
                $agent->approved_at = now();
                $agent->approved_by = $actor->id;
            }
            $agent->save();
            $this->audit($actor, $exists ? 'agent_updated' : 'agent_created', $values['reason'], agent: $agent,
                changes: ['before' => $old, 'after' => $agent->only(array_keys($old)), 'payment_details_changed' => $oldPayment !== $agent->payment_details]);

            return $agent->refresh();
        });
    }

    public function createRule(User $actor, array $data): ReferralCommissionRule
    {
        $this->owner($actor);
        $data = Validator::make($data, [
            'agent_id' => 'required|exists:referral_agents,id', 'hospital_id' => 'nullable|exists:hospitals,id',
            'treatment_id' => 'nullable|exists:treatments,id', 'percentage' => 'required|string',
            'effective_from' => 'required|date', 'reason' => 'required|string|max:1000',
        ])->validate();

        return DB::transaction(function () use ($actor, $data) {
            $rule = ReferralCommissionRule::create(collect($data)->except('percentage')->all() + [
                'rate_bps' => Money::bps($data['percentage']), 'created_by' => $actor->id,
            ]);
            $this->audit($actor, 'rule_created', $data['reason'], agent: $rule->agent, changes: $rule->only(['id', 'hospital_id', 'treatment_id', 'rate_bps', 'effective_from']));

            return $rule;
        });
    }

    public function disableRule(User $actor, ReferralCommissionRule $rule, string $reason): void
    {
        $this->owner($actor);
        $this->reason($reason);
        DB::transaction(function () use ($actor, $rule, $reason) {
            $rule->update(['is_active' => false]);
            $this->audit($actor, 'rule_disabled', $reason, agent: $rule->agent, changes: ['rule_id' => $rule->id]);
        });
    }

    public function createAgreement(User $actor, array $data): HospitalCommissionAgreement
    {
        $this->owner($actor);
        $data = Validator::make($data, [
            'hospital_id' => 'required|exists:hospitals,id', 'treatment_id' => 'nullable|exists:treatments,id',
            'percentage' => 'required|string', 'basis' => 'required|string|max:3000',
            'contract_reference' => 'required|string|max:190', 'effective_from' => 'required|date',
        ])->validate();

        return DB::transaction(function () use ($actor, $data) {
            $agreement = HospitalCommissionAgreement::create(collect($data)->except('percentage')->all() + [
                'rate_bps' => Money::bps($data['percentage']), 'created_by' => $actor->id,
            ]);
            $this->audit($actor, 'agreement_created', $data['contract_reference'], changes: ['agreement_id' => $agreement->id]);

            return $agreement;
        });
    }

    public function resolveRule(ReferralCase $case): ?ReferralCommissionRule
    {
        // Explicit precedence: agent + hospital + treatment, hospital, treatment, agent default.
        return ReferralCommissionRule::where('agent_id', $case->agent_id)->where('is_active', true)
            ->where('effective_from', '<=', now())
            ->where(fn ($q) => $q->whereNull('hospital_id')->orWhere('hospital_id', $case->hospital_id))
            ->where(fn ($q) => $q->whereNull('treatment_id')->orWhere('treatment_id', $case->treatment_id))
            ->orderByRaw('(CASE WHEN hospital_id IS NOT NULL THEN 2 ELSE 0 END + CASE WHEN treatment_id IS NOT NULL THEN 1 ELSE 0 END) DESC')
            ->orderByDesc('effective_from')->orderByDesc('id')->first();
    }

    public function intake(ReferralAgent $agent, array $data, string $source, ?User $actor = null): ReferralCase
    {
        if ($actor) {
            $this->owner($actor);
        }
        $values = Validator::make($data, [
            'name' => 'required|string|max:120', 'phone' => 'required|string|max:32',
            'district' => 'nullable|string|max:100', 'request_summary' => 'nullable|string|max:1000',
            'hospital_id' => 'nullable|exists:hospitals,id', 'treatment_id' => 'nullable|exists:treatments,id',
            'contact_consent' => 'accepted', 'submission_key' => 'required|uuid',
        ])->validate();

        return DB::transaction(function () use ($agent, $values, $source, $actor) {
            $agent = ReferralAgent::lockForUpdate()->findOrFail($agent->id);
            abort_unless($agent->is_active, 404);
            $existing = ReferralCase::where('submission_key', $values['submission_key'])->first();
            if ($existing) {
                abort_unless($existing->agent_id === $agent->id, 409);

                return $existing;
            }
            $patient = ReferralPatient::create([
                'name' => $values['name'], 'phone' => Money::phone($values['phone']),
                'phone_hash' => Money::phoneHash($values['phone']), 'district' => $values['district'] ?? null,
            ]);
            $case = ReferralCase::create([
                'reference' => 'AHC-REF-'.Str::upper(Str::random(12)), 'submission_key' => $values['submission_key'],
                'agent_id' => $agent->id, 'patient_id' => $patient->id, 'source' => $source,
                'hospital_id' => $values['hospital_id'] ?? null, 'treatment_id' => $values['treatment_id'] ?? null,
                'request_summary' => $values['request_summary'] ?? null, 'contact_requested_at' => now(), 'status' => 'new',
            ]);
            $this->audit($actor, 'submitted', 'Contact permission requested via '.$source, $case);

            return $case;
        });
    }

    public function duplicates(ReferralCase $case)
    {
        return ReferralCase::whereKeyNot($case->id)->whereHas('patient', fn ($q) => $q->where('phone_hash', $case->patient->phone_hash));
    }

    public function progress(User $actor, ReferralCase $case, array $data): void
    {
        $data = Validator::make($data, [
            'status' => ['required', Rule::in(array_keys(ReferralCase::statuses()))],
            'hospital_id' => 'nullable|exists:hospitals,id', 'treatment_id' => 'nullable|exists:treatments,id',
            'assigned_to' => 'nullable|exists:users,id', 'agent_id' => 'nullable|exists:referral_agents,id',
            'follow_up_on' => 'nullable|date', 'note' => 'required|string|max:2000',
            'confirm_contact' => 'nullable|boolean', 'confirm_sharing' => 'nullable|boolean',
            'identity_resolution' => ['nullable', Rule::in(['new_patient', 'existing_patient_new_case', 'duplicate'])],
            'related_case_id' => 'nullable|exists:referral_cases,id',
        ])->validate();

        DB::transaction(function () use ($actor, $case, $data) {
            $case = ReferralCase::lockForUpdate()->findOrFail($case->id);
            $this->access($actor, $case);
            $before = $case->only(['status', 'agent_id', 'assigned_to', 'hospital_id', 'treatment_id']);
            if ($actor->isOwner()) {
                if (! empty($data['assigned_to'])) {
                    $staff = User::findOrFail($data['assigned_to']);
                    abort_unless($staff->is_active && $staff->can(AdminAccess::PANEL_PERMISSION), 422);
                }
                $case->assigned_to = $data['assigned_to'] ?? null;
            }
            $requestedAgent = $data['agent_id'] ?? $case->agent_id;
            if ((int) $requestedAgent !== $case->agent_id) {
                $this->owner($actor);
                $this->ensure(! $case->verified_at && ! $case->rate_locked_at, 'যাচাইকৃত কেসের রেফারার বদলানো যাবে না। নতুন অনুরোধ আলাদা কেসে নিন।');
                $this->ensure(ReferralAgent::whereKey($requestedAgent)->where('is_active', true)->exists(), 'সক্রিয় এজেন্ট নির্বাচন করুন।');
                $case->agent_id = $requestedAgent;
            }
            foreach (['hospital_id', 'treatment_id'] as $field) {
                $value = $data[$field] ?? null;
                if ($value != $case->{$field}) {
                    $this->ensure(! $case->verified_at, 'গৃহীত কেসের হাসপাতাল/ট্রিটমেন্ট স্থির। অন্য চিকিৎসার জন্য নতুন কেস তৈরি করুন।');
                    $case->{$field} = $value;
                }
            }
            if (! empty($data['confirm_contact'])) {
                $case->consent_confirmed_at ??= now();
            }
            if (! empty($data['confirm_sharing'])) {
                $this->ensure((bool) $case->consent_confirmed_at, 'আগে রোগী/অভিভাবকের সঙ্গে যোগাযোগের সম্মতি নিশ্চিত করুন।');
                $case->sharing_confirmed_at ??= now();
            }
            if (in_array($data['status'], ['verified', 'hospital_review', 'appointment_confirmed', 'completed'])) {
                $this->ensure((bool) $case->consent_confirmed_at, 'রোগী/অভিভাবকের সঙ্গে যোগাযোগ ও রেফারেল নিশ্চিত করুন।');
                if (! $case->verified_at) {
                    $this->ensure((bool) $case->hospital_id && (bool) $case->treatment_id, 'কেস গ্রহণের আগে হাসপাতাল ও ট্রিটমেন্ট নির্বাচন করুন।');
                    $matches = $this->duplicates($case)->exists();
                    $resolution = $data['identity_resolution'] ?? ($matches ? null : 'new_patient');
                    $this->ensure(in_array($resolution, ['new_patient', 'existing_patient_new_case']), 'একই নম্বরের আগের কেস যাচাই করে পরিচয়ের সিদ্ধান্ত দিন।');
                    if ($matches) {
                        $this->owner($actor);
                    }
                    if ($resolution === 'existing_patient_new_case') {
                        $this->owner($actor);
                        $previous = $this->duplicates($case)->whereKey($data['related_case_id'] ?? 0)->first();
                        $this->ensure((bool) $previous, 'একই নম্বরের সঠিক আগের কেস নির্বাচন করুন।');
                        $case->patient_id = $previous->patient_id;
                    }
                    $case->identity_resolution = $resolution;
                    $case->verified_at = now();
                    if (! $case->rate_locked_at && ($rule = $this->resolveRule($case))) {
                        $case->commission_rate_bps = $rule->rate_bps;
                        $case->commission_rule_id = $rule->id;
                        $case->rate_locked_at = now();
                    }
                    $agreement = HospitalCommissionAgreement::where('hospital_id', $case->hospital_id)
                        ->where(fn ($q) => $q->whereNull('treatment_id')->orWhere('treatment_id', $case->treatment_id))
                        ->where('effective_from', '<=', now())->orderByRaw('CASE WHEN treatment_id IS NOT NULL THEN 1 ELSE 0 END DESC')
                        ->orderByDesc('effective_from')->orderByDesc('id')->first();
                    $case->agreement_snapshot = $agreement?->only(['id', 'hospital_id', 'treatment_id', 'rate_bps', 'basis', 'contract_reference']);
                }
            }
            if (in_array($data['status'], ['hospital_review', 'appointment_confirmed', 'completed'])) {
                $this->ensure((bool) $case->hospital_id && (bool) $case->sharing_confirmed_at, 'হাসপাতাল নির্বাচন এবং তথ্য শেয়ারের আলাদা সম্মতি প্রয়োজন।');
            }
            if ($data['status'] === 'duplicate') {
                $this->owner($actor);
                $this->ensure(! $case->entries()->exists(), 'হিসাব যুক্ত কেস ডুপ্লিকেট করা যাবে না।');
                $related = $this->duplicates($case)->whereKey($data['related_case_id'] ?? 0)->first();
                $this->ensure((bool) $related, 'একই নম্বরের মূল কেস নির্বাচন করুন।');
                $case->duplicate_of_id = $related->id;
                $case->identity_resolution = 'duplicate';
            } elseif ($case->status === 'duplicate') {
                $this->owner($actor);
                $case->duplicate_of_id = null;
            }
            if ($case->status === 'completed' && $data['status'] !== 'completed') {
                $this->owner($actor);
            }
            $case->status = $data['status'];
            $case->follow_up_on = $data['follow_up_on'] ?? null;
            $case->save();
            $this->audit($actor, 'progress_updated', $data['note'], $case, changes: ['before' => $before, 'after' => $case->only(array_keys($before)), 'identity_resolution' => $case->identity_resolution, 'contact_confirmed' => (bool) $case->consent_confirmed_at, 'sharing_confirmed' => (bool) $case->sharing_confirmed_at]);
        });
    }

    public function overrideRate(User $actor, ReferralCase $case, string $percentage, string $reason): void
    {
        $this->owner($actor);
        $this->reason($reason);
        $bps = Money::bps($percentage);
        DB::transaction(function () use ($actor, $case, $bps, $reason) {
            $case = ReferralCase::lockForUpdate()->findOrFail($case->id);
            $balance = $this->balance($case);
            $this->ensure(Money::commission($balance['received'], $bps) >= $balance['paid'], 'ইতোমধ্যে পরিশোধিত পাওনার নিচে হার কমানো যাবে না।');
            $old = $case->commission_rate_bps;
            $case->update(['commission_rate_bps' => $bps, 'commission_rule_id' => null, 'rate_locked_at' => now(),
                'rate_acknowledged_at' => null, 'rate_version' => $case->rate_version + 1, 'approved_minor' => $balance['paid']]);
            $this->audit($actor, 'rate_overridden', $reason, $case, changes: ['before_bps' => $old, 'after_bps' => $bps]);
        });
    }

    public function acknowledge(ReferralAgent|User $actor, ReferralCase $case, int $version, string $reason = ''): void
    {
        if ($actor instanceof User) {
            $this->owner($actor);
            $this->reason($reason);
        }
        DB::transaction(function () use ($actor, $case, $version, $reason) {
            $case = ReferralCase::lockForUpdate()->findOrFail($case->id);
            if ($actor instanceof ReferralAgent) {
                abort_unless($actor->is_active && $case->agent_id === $actor->id, 404);
            }
            $this->ensure($case->commission_rate_bps !== null && $case->rate_version === $version, 'হার পরিবর্তিত হয়েছে। পেজ রিফ্রেশ করে বর্তমান হার দেখুন।');
            $case->update(['rate_acknowledged_at' => now()]);
            $this->audit($actor instanceof User ? $actor : null, 'rate_acknowledged', $reason ?: 'Acknowledged by agent in portal.', $case,
                changes: ['rate_bps' => $case->commission_rate_bps, 'version' => $version, 'agent_actor_id' => $actor instanceof ReferralAgent ? $actor->id : null]);
        });
    }

    public function balance(ReferralCase $case): array
    {
        $totals = $case->entries()->selectRaw('type, SUM(amount_minor) as total')->groupBy('type')->pluck('total', 'type');
        $received = (int) ($totals['receipt'] ?? 0) - (int) ($totals['receipt_reversal'] ?? 0);
        $paid = (int) ($totals['payout'] ?? 0) - (int) ($totals['payout_reversal'] ?? 0);
        $calculated = Money::commission($received, $case->commission_rate_bps ?? 0);
        $eligible = $case->verified_at && $case->status === 'completed' && $case->rate_acknowledged_at;
        $earned = $eligible ? $calculated : 0;
        $approved = min((int) $case->approved_minor, $earned);

        return ['received' => $received, 'calculated' => $calculated, 'earned' => $earned, 'approved' => $approved,
            'paid' => $paid, 'payable' => max(0, $approved - $paid), 'pending' => max(0, $calculated - max($approved, $paid)),
            'overpaid' => max(0, $paid - $calculated)];
    }

    public function approve(User $actor, ReferralCase $case, string $reason): void
    {
        $this->owner($actor);
        $this->reason($reason);
        DB::transaction(function () use ($actor, $case, $reason) {
            $case = ReferralCase::lockForUpdate()->findOrFail($case->id);
            $balance = $this->balance($case);
            $this->ensure($balance['earned'] > 0, 'কেস সম্পন্ন, হার সম্মত এবং হাসপাতালের টাকা প্রাপ্ত হওয়ার পর অনুমোদন দিন।');
            $case->update(['approved_minor' => $balance['earned']]);
            $this->audit($actor, 'commission_approved', $reason, $case, changes: ['approved_minor' => $balance['earned']]);
        });
    }

    public function entry(User $actor, ReferralCase $case, array $data): ReferralLedgerEntry
    {
        $this->owner($actor);
        $data['reference'] = trim(is_string($data['reference'] ?? null) ? $data['reference'] : '');
        $data = Validator::make($data, [
            'type' => ['required', Rule::in(['receipt', 'payout'])], 'amount' => 'required|string',
            'reference' => 'required|string|max:120', 'occurred_on' => 'required|date|before_or_equal:today',
            'operation_key' => 'required|uuid', 'note' => 'required|string|max:2000',
            'original_currency' => ['nullable', Rule::in(['BDT', 'INR', 'USD', 'THB', 'SGD', 'MYR', 'CNY'])],
            'original_amount' => 'nullable|string',
        ])->validate();

        return DB::transaction(function () use ($actor, $case, $data) {
            $case = ReferralCase::lockForUpdate()->findOrFail($case->id);
            if ($prior = ReferralLedgerEntry::where('operation_key', $data['operation_key'])->first()) {
                abort_unless($prior->case_id === $case->id, 409);

                return $prior;
            }
            $amount = Money::minor($data['amount']);
            $this->ensure($amount > 0, 'টাকার অঙ্ক শূন্যের বেশি হতে হবে।');
            $this->ensure(! $case->entries()->where('type', $data['type'])->where('reference', trim($data['reference']))->exists(), 'এই ট্রানজ্যাকশন রেফারেন্স ইতোমধ্যে নথিভুক্ত হয়েছে।');
            if ($data['type'] === 'payout') {
                $this->ensure($amount <= $this->balance($case)['payable'], 'অনুমোদিত অপরিশোধিত পাওনার বেশি পেমেন্ট দেওয়া যাবে না।');
                $this->ensure((bool) $case->agent->payment_details, 'আগে এজেন্টের যাচাইকৃত পেমেন্ট তথ্য সংরক্ষণ করুন।');
            } else {
                $this->ensure((bool) $case->hospital_id && (bool) $case->verified_at && $case->status !== 'duplicate', 'যাচাইকৃত কেসে হাসপাতাল নির্বাচন প্রয়োজন।');
                $this->ensure(! empty($data['original_currency']) && ! empty($data['original_amount']), 'মূল মুদ্রা ও প্রাপ্ত অঙ্ক প্রয়োজন।');
            }
            $original = ! empty($data['original_amount']) ? Money::minor($data['original_amount'], 'original_amount') : null;
            if ($data['type'] === 'receipt') {
                $this->ensure($original > 0, 'মূল মুদ্রায় প্রাপ্ত অঙ্ক শূন্যের বেশি হতে হবে।');
            }
            if ($data['type'] === 'receipt' && $data['original_currency'] === 'BDT') {
                $this->ensure($original === $amount, 'BDT হলে মূল অঙ্ক ও হিসাবের অঙ্ক একই হতে হবে।');
            }
            $entry = $case->entries()->create(collect($data)->except(['amount', 'original_amount'])->all() + [
                'amount_minor' => $amount, 'original_minor' => $original, 'created_by' => $actor->id,
                'payee_snapshot' => $data['type'] === 'payout' ? $case->agent->payment_details : null,
            ]);
            $this->audit($actor, $data['type'].'_recorded', $data['note'], $case, changes: ['entry_id' => $entry->id, 'amount_minor' => $amount]);

            return $entry;
        });
    }

    public function reverse(User $actor, ReferralCase $case, ReferralLedgerEntry $entry, string $reason): void
    {
        $this->owner($actor);
        $this->reason($reason);
        DB::transaction(function () use ($actor, $case, $entry, $reason) {
            $case = ReferralCase::lockForUpdate()->findOrFail($case->id);
            abort_unless($entry->case_id === $case->id, 404);
            $this->ensure(in_array($entry->type, ['receipt', 'payout']) && ! $entry->reversal()->exists(), 'এই এন্ট্রি ইতোমধ্যে রিভার্স হয়েছে অথবা রিভার্স করা যায় না।');
            $case->entries()->create(['type' => $entry->type.'_reversal', 'amount_minor' => $entry->amount_minor,
                'reference' => 'REV-'.$entry->id, 'operation_key' => (string) Str::uuid(), 'occurred_on' => today(),
                'reverses_id' => $entry->id, 'created_by' => $actor->id, 'note' => $reason]);
            $balance = $this->balance($case);
            $case->update(['approved_minor' => min((int) $case->approved_minor, $balance['earned'])]);
            $this->audit($actor, 'ledger_reversed', $reason, $case, changes: ['entry_id' => $entry->id]);
        });
    }

    private function reason(string $reason): void
    {
        Validator::make(['reason' => $reason], ['reason' => 'required|string|max:2000'])->validate();
    }

    private function ensure(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['referral' => $message]);
        }
    }
}
