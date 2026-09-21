<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_agents', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('business_name');
            $table->string('email')->unique();
            $table->string('phone', 32);
            $table->string('district');
            $table->text('address')->nullable();
            $table->text('payment_details')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('session_version')->default(1);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('agent_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('referral_patients', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('phone');
            $table->string('phone_hash', 64)->index();
            $table->string('district')->nullable();
            $table->timestamps();
        });
        Schema::create('referral_commission_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('referral_agents')->restrictOnDelete();
            $table->foreignId('hospital_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('treatment_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('rate_bps');
            $table->timestamp('effective_from')->index();
            $table->boolean('is_active')->default(true);
            $table->string('reason', 1000);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
        Schema::create('hospital_commission_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->restrictOnDelete();
            $table->foreignId('treatment_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('rate_bps');
            $table->text('basis');
            $table->string('contract_reference');
            $table->timestamp('effective_from');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
        Schema::create('referral_cases', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->uuid('submission_key')->unique();
            $table->foreignId('agent_id')->constrained('referral_agents')->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('referral_patients')->restrictOnDelete();
            $table->foreignId('hospital_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('treatment_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('request_summary')->nullable();
            $table->string('source', 20);
            $table->string('status', 32)->default('new')->index();
            $table->timestamp('contact_requested_at');
            $table->timestamp('consent_confirmed_at')->nullable();
            $table->timestamp('sharing_confirmed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('duplicate_of_id')->nullable()->constrained('referral_cases')->restrictOnDelete();
            $table->string('identity_resolution')->nullable();
            $table->date('follow_up_on')->nullable()->index();
            $table->unsignedSmallInteger('commission_rate_bps')->nullable();
            $table->foreignId('commission_rule_id')->nullable()->constrained('referral_commission_rules')->restrictOnDelete();
            $table->timestamp('rate_locked_at')->nullable();
            $table->unsignedInteger('rate_version')->default(1);
            $table->timestamp('rate_acknowledged_at')->nullable();
            $table->json('agreement_snapshot')->nullable();
            $table->unsignedBigInteger('approved_minor')->default(0);
            $table->timestamps();
            $table->index(['agent_id', 'status']);
        });
        Schema::create('referral_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('referral_cases')->restrictOnDelete();
            $table->uuid('operation_key')->unique();
            $table->string('type', 24);
            $table->unsignedBigInteger('amount_minor');
            $table->string('reference', 120);
            $table->date('occurred_on');
            $table->string('original_currency', 3)->nullable();
            $table->unsignedBigInteger('original_minor')->nullable();
            $table->foreignId('reverses_id')->nullable()->unique()->constrained('referral_ledger_entries')->restrictOnDelete();
            $table->text('note');
            $table->text('payee_snapshot')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['case_id', 'type', 'reference'], 'referral_ledger_reference_unique');
        });
        Schema::create('referral_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->nullable()->constrained('referral_cases')->restrictOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('referral_agents')->restrictOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 64);
            $table->text('note');
            $table->json('changes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        foreach (['referral_audits', 'referral_ledger_entries', 'referral_cases', 'hospital_commission_agreements', 'referral_commission_rules', 'referral_patients', 'agent_password_reset_tokens', 'referral_agents'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
