<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralCommissionRule extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['effective_from' => 'datetime', 'is_active' => 'boolean', 'rate_bps' => 'integer'];
    }

    public function agent()
    {
        return $this->belongsTo(ReferralAgent::class, 'agent_id');
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }
}
