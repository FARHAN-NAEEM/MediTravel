<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralCase extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['request_summary' => 'encrypted', 'agreement_snapshot' => 'array', 'contact_requested_at' => 'datetime', 'consent_confirmed_at' => 'datetime', 'sharing_confirmed_at' => 'datetime', 'verified_at' => 'datetime', 'rate_locked_at' => 'datetime', 'rate_acknowledged_at' => 'datetime', 'follow_up_on' => 'date'];
    }

    public function agent()
    {
        return $this->belongsTo(ReferralAgent::class, 'agent_id');
    }

    public function patient()
    {
        return $this->belongsTo(ReferralPatient::class, 'patient_id');
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function entries()
    {
        return $this->hasMany(ReferralLedgerEntry::class, 'case_id');
    }

    public function audits()
    {
        return $this->hasMany(ReferralAudit::class, 'case_id')->latest('id');
    }

    public static function statuses(): array
    {
        return ['new' => 'নতুন', 'contacted' => 'যোগাযোগ হয়েছে', 'verified' => 'যাচাইকৃত', 'hospital_review' => 'হাসপাতালের সঙ্গে সমন্বয়', 'appointment_confirmed' => 'অ্যাপয়েন্টমেন্ট নিশ্চিত', 'completed' => 'সেবা সম্পন্ন', 'cancelled' => 'বাতিল', 'duplicate' => 'ডুপ্লিকেট'];
    }
}
