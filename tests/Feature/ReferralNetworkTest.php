<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Department;
use App\Models\Hospital;
use App\Models\ReferralAgent;
use App\Models\ReferralAudit;
use App\Models\ReferralCase;
use App\Models\ReferralLedgerEntry;
use App\Models\Treatment;
use App\Models\User;
use App\Notifications\AgentPasswordReset;
use App\Services\ReferralNetwork;
use App\Support\AdminAccess;
use App\Support\ReferralMoney as Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReferralNetworkTest extends TestCase
{
    use RefreshDatabase;

    private ReferralNetwork $network;

    private User $owner;

    private ReferralAgent $agent;

    private Hospital $hospital;

    private Treatment $treatment;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->network = app(ReferralNetwork::class);
        $this->owner = $this->staff('owner@example.test');
        DB::table('users')->where('id', $this->owner->id)->update(['is_owner' => true]);
        $this->owner->refresh();
        $this->agent = $this->network->saveAgent($this->owner, $this->agentData());
        $country = Country::create(['name' => 'India', 'slug' => 'india']);
        $city = City::create(['name' => 'Chennai', 'slug' => 'chennai', 'country_id' => $country->id]);
        $this->hospital = Hospital::create(['country_id' => $country->id, 'city_id' => $city->id, 'name' => 'Test Hospital', 'slug' => 'test-hospital']);
        $department = Department::create(['name' => 'Cardiac care', 'slug' => 'cardiac-care']);
        $this->treatment = Treatment::create(['name' => 'Cardiac treatment', 'slug' => 'cardiac-treatment', 'department_id' => $department->id]);
    }

    public function test_rates_use_specificity_effective_dates_and_agent_isolation(): void
    {
        $case = $this->intake();
        $this->assertNull($this->network->resolveRule($case));
        $default = $this->rule('10');
        $this->assertSame($default->id, $this->network->resolveRule($case)->id);
        $treatment = $this->rule('30', ['treatment_id' => $this->treatment->id]);
        $this->assertSame($treatment->id, $this->network->resolveRule($case)->id);
        $hospital = $this->rule('20', ['hospital_id' => $this->hospital->id]);
        $this->assertSame($hospital->id, $this->network->resolveRule($case)->id);
        $both = $this->rule('35.25', ['hospital_id' => $this->hospital->id, 'treatment_id' => $this->treatment->id]);
        $this->assertSame(3525, $this->network->resolveRule($case)->rate_bps);
        $this->rule('99', ['effective_from' => now()->addDay()->toDateTimeString(), 'hospital_id' => $this->hospital->id, 'treatment_id' => $this->treatment->id]);
        $this->rule('98', ['agent_id' => $this->otherAgent()->id, 'hospital_id' => $this->hospital->id, 'treatment_id' => $this->treatment->id]);
        $this->assertSame($both->id, $this->network->resolveRule($case)->id);
        $this->network->disableRule($this->owner, $both, 'End this special rate');
        $this->assertSame($hospital->id, $this->network->resolveRule($case)->id);
    }

    public function test_case_rate_and_hospital_agreement_are_snapshots_not_retroactive_rules(): void
    {
        $this->rule('20');
        $this->network->createAgreement($this->owner, ['hospital_id' => $this->hospital->id, 'percentage' => '8', 'basis' => 'Contract eligible services only', 'contract_reference' => 'PRIVATE-CONTRACT', 'effective_from' => now()->toDateTimeString()]);
        $case = $this->intake();
        $this->progress($case, 'completed');
        $this->rule('30');
        $this->assertSame(2000, $case->fresh()->commission_rate_bps);
        $this->assertSame(800, $case->fresh()->agreement_snapshot['rate_bps']);
        $new = $this->intake(['phone' => '01812345678']);
        $this->progress($new, 'verified');
        $this->assertSame(3000, $new->fresh()->commission_rate_bps);
        $this->network->acknowledge($this->agent, $case->fresh(), 1);
        $this->network->overrideRate($this->owner, $case, '25.50', 'Negotiated per patient');
        $case->refresh();
        $this->assertSame(2550, $case->commission_rate_bps);
        $this->assertNull($case->rate_acknowledged_at);
        $this->invalid(fn () => $this->network->acknowledge($this->agent, $case, 1));
        $this->network->acknowledge($this->agent, $case, 2);
        $this->assertNotNull($case->fresh()->rate_acknowledged_at);
        $this->invalid(fn () => $this->progress($case, 'completed', ['hospital_id' => null]));
    }

    public function test_earnings_require_received_commission_completed_case_acceptance_and_approval(): void
    {
        $this->rule('20');
        $case = $this->intake();
        $this->progress($case, 'verified');
        $this->receipt($case, '10000');
        $this->assertSame(200000, $this->network->balance($case->fresh())['calculated']);
        $this->invalid(fn () => $this->network->approve($this->owner, $case, 'Not completed'));
        $this->progress($case, 'completed');
        $this->invalid(fn () => $this->network->approve($this->owner, $case, 'No acceptance'));
        $this->network->acknowledge($this->agent, $case->fresh(), 1);
        $this->invalid(fn () => $this->payout($case, '1'));
        $this->network->approve($this->owner, $case, 'Receipt checked');
        $this->assertSame(200000, $this->network->balance($case->fresh())['payable']);
        $this->payout($case, '500');
        $this->assertSame(150000, $this->network->balance($case->fresh())['payable']);
        $this->invalid(fn () => $this->payout($case, '1500.01'));
        $this->payout($case, '1500');
        $this->assertSame(0, $this->network->balance($case->fresh())['payable']);
        $this->invalid(fn () => $this->network->overrideRate($this->owner, $case, '19.99', 'Already paid'));
    }

    public function test_multiple_receipts_round_cumulatively_and_never_mix_currencies(): void
    {
        $case = $this->ready('33.33');
        $this->receipt($case, '0.01');
        $this->receipt($case, '0.01');
        $this->assertSame(1, $this->network->balance($case->fresh())['calculated']);
        $this->network->approve($this->owner, $case, 'Cumulative receipt');
        $this->payout($case, '0.01');
        $this->receipt($case, '0.01');
        $this->network->approve($this->owner, $case, 'Additional receipt');
        $this->assertSame(0, $this->network->balance($case->fresh())['payable']);
        $this->receipt($case, '100', ['original_currency' => 'INR', 'original_amount' => '70']);
        $this->assertSame(10003, $this->network->balance($case->fresh())['received']);
        $this->invalid(fn () => $this->receipt($case, '100', ['original_amount' => '99']));
        $this->invalid(fn () => $this->receipt($case, '100', ['original_currency' => 'USD', 'original_amount' => '0.00']));
    }

    public function test_idempotency_reference_normalization_and_reversals_preserve_history(): void
    {
        $case = $this->ready();
        $key = (string) Str::uuid();
        $entry = $this->receipt($case, '1000', ['operation_key' => $key, 'reference' => ' BANK-123 ']);
        $same = $this->receipt($case, '1000', ['operation_key' => $key, 'reference' => 'BANK-123']);
        $this->assertSame($entry->id, $same->id);
        $this->assertSame('BANK-123', $entry->reference);
        $this->invalid(fn () => $this->receipt($case, '1000', ['reference' => ' BANK-123 ']));
        $this->network->approve($this->owner, $case, 'Checked');
        $paid = $this->payout($case, '200');
        $this->network->reverse($this->owner, $case, $entry, 'Hospital reversed transfer');
        $balance = $this->network->balance($case->fresh());
        $this->assertSame(20000, $balance['overpaid']);
        $this->assertSame(0, $balance['payable']);
        $this->invalid(fn () => $this->network->reverse($this->owner, $case, $entry, 'Duplicate reversal'));
        $this->invalid(fn () => $this->payout($case, '0.01'));
        $this->network->reverse($this->owner, $case, $paid, 'Agent returned transfer');
        $this->assertSame(0, $this->network->balance($case->fresh())['overpaid']);
        $this->assertDatabaseCount('referral_ledger_entries', 4);
        $this->assertDatabaseHas('referral_audits', ['action' => 'ledger_reversed', 'case_id' => $case->id]);
        try {
            $entry->update(['amount_minor' => 9]);
            $this->fail('Ledger was mutable');
        } catch (\LogicException) {
            $this->assertTrue(true);
        }
        try {
            ReferralAudit::first()->delete();
            $this->fail('Audit was deletable');
        } catch (\LogicException) {
            $this->assertTrue(true);
        }
    }

    public function test_intake_encrypts_private_data_and_deduplicates_submission_not_people(): void
    {
        $key = (string) Str::uuid();
        $case = $this->intake(['submission_key' => $key, 'request_summary' => 'PRIVATE-MEDICAL-SUMMARY']);
        $same = $this->intake(['submission_key' => $key]);
        $this->assertSame($case->id, $same->id);
        $this->assertDatabaseCount('referral_patients', 1);
        $row = DB::table('referral_patients')->first();
        $this->assertNotSame('Patient Test', $row->name);
        $this->assertNotSame('8801712345678', $row->phone);
        $this->assertStringNotContainsString('PRIVATE-MEDICAL-SUMMARY', DB::table('referral_cases')->value('request_summary'));
        $second = $this->intake(['phone' => '+880 1712 345678', 'name' => 'Another family member']);
        $this->assertSame(1, $this->network->duplicates($second)->count());
        $this->invalid(fn () => $this->progress($second, 'verified'));
        $this->progress($second, 'verified', ['identity_resolution' => 'new_patient']);
        $this->assertNotSame($case->patient_id, $second->fresh()->patient_id);
        $third = $this->intake();
        $this->progress($third, 'verified', ['identity_resolution' => 'existing_patient_new_case', 'related_case_id' => $case->id]);
        $this->assertSame($case->patient_id, $third->fresh()->patient_id);
        $fourth = $this->intake();
        $this->progress($fourth, 'duplicate', ['related_case_id' => $case->id]);
        $this->assertSame($case->id, $fourth->fresh()->duplicate_of_id);
        $this->assertSame(0, $this->network->balance($fourth->fresh())['earned']);
    }

    public function test_contact_and_hospital_sharing_permissions_are_separate(): void
    {
        $this->invalid(fn () => $this->intake(['contact_consent' => false]));
        $case = $this->intake();
        $this->invalid(fn () => $this->progress($case, 'verified', ['confirm_contact' => false, 'confirm_sharing' => false]));
        $this->progress($case, 'verified', ['confirm_sharing' => false]);
        $this->invalid(fn () => $this->progress($case, 'hospital_review', ['confirm_sharing' => false]));
        $this->progress($case, 'hospital_review');
        $this->assertNotNull($case->fresh()->sharing_confirmed_at);
    }

    public function test_public_link_uses_only_active_referrer_and_requires_consent(): void
    {
        $url = route('referral.submit', $this->agent->code);
        $this->get($url)->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->post($url, $this->intakeData(['contact_consent' => false]))->assertSessionHasErrors('contact_consent');
        $this->post($url, $this->intakeData(['agent_id' => $this->otherAgent()->id]))->assertRedirect()->assertSessionHas('referral_saved');
        $this->assertSame($this->agent->id, ReferralCase::first()->agent_id);
        $this->agent->update(['is_active' => false]);
        $this->get($url)->assertNotFound();
        $this->post($url, $this->intakeData())->assertNotFound();
    }

    public function test_agent_cannot_access_other_cases_internal_medical_or_hospital_finances(): void
    {
        $case = $this->ready();
        $case->update(['request_summary' => 'SECRET-DIAGNOSIS', 'agreement_snapshot' => ['contract_reference' => 'SECRET-HOSPITAL-CONTRACT']]);
        $this->receipt($case, '12345', ['note' => 'SECRET-RECEIPT-NOTE', 'reference' => 'SECRET-BANK-REF']);
        $other = $this->network->intake($this->otherAgent(), $this->intakeData(['name' => 'OTHER-PATIENT']), 'agent');
        $this->actingAs($this->agent, 'agent')->withSession(['agent_session_version' => 1]);
        $this->get('/agent')->assertOk()->assertSee($case->reference)->assertDontSee($other->reference)->assertDontSee('OTHER-PATIENT');
        $this->get(route('agent.show', $case->reference))->assertOk()->assertDontSee('SECRET-DIAGNOSIS')->assertDontSee('SECRET-HOSPITAL-CONTRACT')->assertDontSee('SECRET-RECEIPT-NOTE')->assertDontSee('SECRET-BANK-REF');
        $this->get(route('agent.show', $other->reference))->assertNotFound();
        $this->post(route('agent.acknowledge', $other->reference), ['rate_version' => 1, 'agree' => 1])->assertNotFound();
        $csv = $this->get('/agent/statement')->assertOk()->streamedContent();
        $this->assertStringContainsString($case->reference, $csv);
        $this->assertStringNotContainsString($other->reference, $csv);
        $this->assertStringNotContainsString('SECRET-', $csv);
        $this->get('/operations/referrals')->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_staff_only_see_assigned_cases_and_cannot_change_finance_or_agents(): void
    {
        $staff = $this->staff('staff@example.test');
        $case = $this->ready();
        $case->update(['assigned_to' => $staff->id]);
        $other = $this->intake(['phone' => '01912345678']);
        $this->actingAs($staff, 'web')->withSession(['admin_session_version' => $staff->session_version]);
        $this->get('/operations/referrals')->assertOk()->assertSee($case->reference)->assertDontSee($other->reference);
        $this->get(route('referral-ops.show', $case))->assertOk()->assertDontSee('name="action" value="entry"', false);
        $this->get(route('referral-ops.show', $other))->assertForbidden();
        foreach (['agents', 'rules', 'audit', 'new'] as $path) {
            $this->get('/operations/referrals/'.$path)->assertForbidden();
        }
        $this->post(route('referral-ops.finance', $case), ['action' => 'override', 'percentage' => '100', 'reason' => 'Forbidden'])->assertForbidden();
        $this->post(route('referral-ops.progress', $case), $this->progressData($case, 'completed', ['assigned_to' => $this->owner->id]))->assertRedirect();
        $this->assertSame($staff->id, $case->fresh()->assigned_to);
    }

    public function test_owner_screens_render_and_manual_intake_does_not_send_email(): void
    {
        $case = $this->ready();
        $this->rule('30', ['treatment_id' => $this->treatment->id]);
        $this->receipt($case, '1000');
        $this->network->approve($this->owner, $case, 'Checked');
        $this->payout($case, '50');
        $this->actingAs($this->owner, 'web')->withSession(['admin_session_version' => $this->owner->session_version]);
        foreach (['', '/agents', '/agents?edit='.$this->agent->id, '/rules', '/audit', '/new', '/cases/'.$case->id] as $path) {
            $this->get('/operations/referrals'.$path)->assertOk();
        }
        $this->post('/operations/referrals/new', $this->intakeData(['phone' => '01612345678', 'agent_id' => $this->agent->id]))->assertRedirect();
        $this->assertDatabaseCount('referral_cases', 2);
        Notification::assertNothingSent();
        $this->post(route('referral-ops.agents.invite', $this->agent))->assertRedirect();
        Notification::assertSentTo($this->agent, AgentPasswordReset::class);
    }

    public function test_agent_password_broker_is_separate_and_tokens_are_single_use(): void
    {
        $token = Password::broker('agents')->createToken($this->agent);
        $data = ['email' => $this->agent->email, 'token' => $token, 'password' => 'Testing-Password-123!', 'password_confirmation' => 'Testing-Password-123!'];
        $this->assertDatabaseCount('password_reset_tokens', 0);
        $this->get('/agent/reset-password/'.$token.'?email='.$this->agent->email)->assertOk();
        $this->post('/agent/reset-password', $data)->assertRedirect('/agent/login');
        $this->assertTrue(Hash::check($data['password'], $this->agent->fresh()->password));
        $this->assertSame(2, $this->agent->fresh()->session_version);
        $this->post('/agent/reset-password', $data)->assertSessionHasErrors('email');
        $this->post('/agent/login', ['email' => strtoupper($this->agent->email), 'password' => $data['password']])->assertRedirect('/agent');
        $this->get('/agent')->assertOk();
        $this->assertNull(Auth::guard('web')->user());
    }

    public function test_disabling_or_changing_email_revokes_sessions_and_reset_tokens(): void
    {
        Password::broker('agents')->createToken($this->agent);
        $this->actingAs($this->agent, 'agent')->withSession(['agent_session_version' => 1]);
        $this->network->saveAgent($this->owner, $this->agentData(['is_active' => false]), $this->agent);
        $this->assertDatabaseCount('agent_password_reset_tokens', 0);
        Auth::guard('agent')->setUser($this->agent->fresh());
        $this->get('/agent')->assertRedirect('/agent/login');
        $this->network->saveAgent($this->owner, $this->agentData(['is_active' => true]), $this->agent);
        Auth::guard('agent')->setUser($this->agent->fresh());
        $this->withSession(['agent_session_version' => 1])->get('/agent')->assertRedirect('/agent/login');
    }

    public function test_forgot_password_responses_do_not_reveal_accounts(): void
    {
        $this->get('/agent/login')->assertOk();
        $this->get('/agent/forgot-password')->assertOk();
        $this->post('/agent/forgot-password', ['email' => $this->agent->email])->assertRedirect()->assertSessionHas('success');
        $first = session('success');
        $this->post('/agent/forgot-password', ['email' => 'missing@example.test'])->assertRedirect()->assertSessionHas('success', $first);
        Notification::assertSentTo($this->agent, AgentPasswordReset::class);
        $this->assertSame(1, Notification::sent($this->agent, AgentPasswordReset::class)->count());
    }

    public function test_rate_and_money_boundaries_are_exact(): void
    {
        $this->assertSame(0, Money::bps('0'));
        $this->assertSame(10000, Money::bps('100.00'));
        $this->assertSame(12345678912, Money::minor('123456789.12'));
        $this->assertSame(1, Money::commission(1, 5000));
        foreach (['-1', '100.01', 'abc', '2.345', '1e2', 'NaN'] as $value) {
            $this->invalid(fn () => Money::bps($value));
        }
        $this->assertSame(Money::phoneHash('01712345678'), Money::phoneHash('008801712345678'));
        $this->invalid(fn () => Money::phone('123'));
    }

    public function test_case_override_before_verification_preserves_selection_and_requires_context(): void
    {
        $case = $this->intake(['hospital_id' => null, 'treatment_id' => null]);
        $this->network->overrideRate($this->owner, $case, '30', 'Negotiated for this referral');
        $this->invalid(fn () => $this->progress($case, 'verified'));
        $this->progress($case, 'verified', ['hospital_id' => $this->hospital->id, 'treatment_id' => $this->treatment->id]);
        $case->refresh();
        $this->assertSame($this->hospital->id, $case->hospital_id);
        $this->assertSame(3000, $case->commission_rate_bps);
        $this->assertNull($case->commission_rule_id);
    }

    public function test_new_receipts_and_rate_changes_require_new_approval(): void
    {
        $case = $this->ready();
        $this->receipt($case, '1000');
        $this->network->approve($this->owner, $case, 'First approval');
        $this->payout($case, '50');
        $this->receipt($case, '1000');
        $this->assertSame(15000, $this->network->balance($case->fresh())['payable']);
        $this->network->overrideRate($this->owner, $case, '30', 'New negotiated terms');
        $this->assertSame(0, $this->network->balance($case->fresh())['payable']);
        $this->network->acknowledge($this->agent, $case->fresh(), 2);
        $this->assertSame(0, $this->network->balance($case->fresh())['payable']);
        $this->network->approve($this->owner, $case, 'Re-approved current terms');
        $this->assertSame(55000, $this->network->balance($case->fresh())['payable']);
    }

    public function test_agent_login_is_rate_limited_and_admin_token_cannot_reset_agent(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/agent/login', ['email' => $this->agent->email, 'password' => 'wrong-password'])->assertSessionHasErrors('email');
        }
        $this->assertGuest('agent');
        $admin = $this->staff($this->agent->email);
        $token = Password::broker('users')->createToken($admin);
        $this->post('/agent/reset-password', ['email' => $this->agent->email, 'token' => $token, 'password' => 'Testing-Password-123!', 'password_confirmation' => 'Testing-Password-123!'])->assertSessionHasErrors('email');
        $this->assertSame(1, $this->agent->fresh()->session_version);
    }

    public function test_staff_cannot_use_any_owner_mutation_endpoint(): void
    {
        $staff = $this->staff('limited@example.test');
        $case = $this->intake();
        $case->update(['assigned_to' => $staff->id]);
        $rule = $this->rule('20');
        $this->actingAs($staff, 'web')->withSession(['admin_session_version' => 1]);
        foreach (['/agents' => $this->agentData(), '/rules' => [], '/agreements' => [], '/new' => $this->intakeData(), '/agents/'.$this->agent->id.'/invite' => [], '/rules/'.$rule->id.'/disable' => ['reason' => 'Forged request']] as $path => $data) {
            $this->post('/operations/referrals'.$path, $data)->assertForbidden();
        }
        $this->post(route('referral-ops.progress', $case), $this->progressData($case, 'contacted', ['agent_id' => $this->otherAgent()->id]))->assertForbidden();
        $this->assertSame($this->agent->id, $case->fresh()->agent_id);
        $this->assertTrue($rule->fresh()->is_active);
        Notification::assertNothingSent();
    }

    public function test_public_intake_ignores_forged_financial_fields_and_escapes_html(): void
    {
        $name = '<script>alert("patient")</script>';
        $this->post(route('referral.submit', $this->agent->code), $this->intakeData([
            'name' => $name, 'commission_rate_bps' => 10000, 'approved_minor' => 999999,
            'status' => 'completed', 'verified_at' => now(), 'assigned_to' => $this->owner->id,
        ]))->assertRedirect();
        $case = ReferralCase::firstOrFail();
        $this->assertSame('new', $case->status);
        $this->assertNull($case->commission_rate_bps);
        $this->assertNull($case->verified_at);
        $this->assertNull($case->assigned_to);
        $this->assertSame(0, $case->approved_minor);
        $this->actingAs($this->agent, 'agent')->withSession(['agent_session_version' => 1]);
        $response = $this->get(route('agent.show', $case->reference))->assertOk()
            ->assertSee($name)->assertDontSee($name, false)
            ->assertHeader('X-Frame-Options', 'DENY')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_referral_posts_require_csrf_tokens(): void
    {
        // Laravel skips CSRF only in testing; exercise the real middleware path.
        $this->app['env'] = 'local';
        $this->post('/agent/login', ['email' => $this->agent->email, 'password' => 'Wrong'])->assertStatus(419);
        $this->post(route('referral.submit', $this->agent->code), $this->intakeData())->assertStatus(419);
        $this->actingAs($this->owner, 'web')->withSession(['admin_session_version' => 1]);
        $this->post('/operations/referrals/agents', $this->agentData())->assertStatus(419);
        $this->assertDatabaseCount('referral_cases', 0);
    }

    public function test_agent_reset_links_use_trusted_host_and_expired_tokens_fail(): void
    {
        config(['app.url' => 'https://asianhealthconnect.com']);
        $token = Password::broker('agents')->createToken($this->agent);
        $message = (new AgentPasswordReset($token))->toMail($this->agent);
        $this->assertStringStartsWith('https://asianhealthconnect.com/agent/reset-password/', $message->actionUrl);
        $this->travel(61)->minutes();
        $this->post('/agent/reset-password', ['email' => $this->agent->email, 'token' => $token, 'password' => 'New-Password-123!', 'password_confirmation' => 'New-Password-123!'])->assertSessionHasErrors('email');
        $this->assertSame(1, $this->agent->fresh()->session_version);
        $this->travelBack();
        $this->post('/agent/forgot-password', ['email' => "victim@example.test\r\nBcc: other@example.test"])->assertSessionHasErrors('email');
        Notification::assertNothingSent();
    }

    private function staff(string $email): User
    {
        $user = User::create(['name' => 'Test Staff', 'email' => $email, 'password' => 'Testing-Password-123!']);
        DB::table('users')->where('id', $user->id)->update(['is_active' => true, 'session_version' => 1]);
        $user->givePermissionTo(Permission::findOrCreate(AdminAccess::PANEL_PERMISSION, 'web'));

        return $user->fresh();
    }

    private function agentData(array $changes = []): array
    {
        return array_replace(['name' => 'Agent Test', 'business_name' => 'Test Referral Office', 'email' => 'agent@example.test', 'phone' => '01712345678', 'district' => 'Dhaka', 'payment_details' => 'VERIFIED-PAYEE-ACCOUNT', 'is_active' => true, 'reason' => 'Approved agreement'], $changes);
    }

    private function otherAgent(): ReferralAgent
    {
        return $this->network->saveAgent($this->owner, $this->agentData(['email' => Str::random(10).'@example.test']));
    }

    private function rule(string $rate, array $changes = [])
    {
        return $this->network->createRule($this->owner, array_replace(['agent_id' => $this->agent->id, 'percentage' => $rate, 'effective_from' => now()->toDateTimeString(), 'reason' => 'Negotiated rate'], $changes));
    }

    private function intakeData(array $changes = []): array
    {
        return array_replace(['name' => 'Patient Test', 'phone' => '01712345678', 'district' => 'Dhaka', 'hospital_id' => $this->hospital->id, 'treatment_id' => $this->treatment->id, 'submission_key' => (string) Str::uuid(), 'contact_consent' => true], $changes);
    }

    private function intake(array $changes = []): ReferralCase
    {
        return $this->network->intake($this->agent, $this->intakeData($changes), 'agent');
    }

    private function progressData(ReferralCase $case, string $status, array $changes = []): array
    {
        return array_replace(['status' => $status, 'hospital_id' => $case->hospital_id, 'treatment_id' => $case->treatment_id, 'confirm_contact' => true, 'confirm_sharing' => true, 'note' => 'Confirmed with patient'], $changes);
    }

    private function progress(ReferralCase $case, string $status, array $changes = []): void
    {
        $this->network->progress($this->owner, $case->fresh(), $this->progressData($case, $status, $changes));
    }

    private function ready(string $rate = '20'): ReferralCase
    {
        $this->rule($rate);
        $case = $this->intake();
        $this->progress($case, 'completed');
        $this->network->acknowledge($this->agent, $case->fresh(), 1);

        return $case->fresh();
    }

    private function receipt(ReferralCase $case, string $amount, array $changes = []): ReferralLedgerEntry
    {
        return $this->network->entry($this->owner, $case, array_replace(['type' => 'receipt', 'amount' => $amount, 'original_currency' => 'BDT', 'original_amount' => $amount, 'reference' => Str::random(20), 'occurred_on' => today()->toDateString(), 'operation_key' => (string) Str::uuid(), 'note' => 'Bank credit checked'], $changes));
    }

    private function payout(ReferralCase $case, string $amount): ReferralLedgerEntry
    {
        return $this->network->entry($this->owner, $case, ['type' => 'payout', 'amount' => $amount, 'reference' => Str::random(20), 'occurred_on' => today()->toDateString(), 'operation_key' => (string) Str::uuid(), 'note' => 'Payment checked']);
    }

    private function invalid(callable $call): void
    {
        try {
            $call();
            $this->fail('Expected validation rejection');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }
    }
}
