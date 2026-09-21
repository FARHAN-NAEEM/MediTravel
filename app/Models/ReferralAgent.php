<?php

namespace App\Models;

use App\Notifications\AgentPasswordReset;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class ReferralAgent extends Authenticatable
{
    use Notifiable;

    protected $guarded = ['id'];

    protected $hidden = ['password', 'remember_token', 'payment_details'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'is_active' => 'boolean', 'approved_at' => 'datetime', 'payment_details' => 'encrypted'];
    }

    public function cases()
    {
        return $this->hasMany(ReferralCase::class, 'agent_id');
    }

    public function rules()
    {
        return $this->hasMany(ReferralCommissionRule::class, 'agent_id');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notifyNow(new AgentPasswordReset($token));
    }
}
