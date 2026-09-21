<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalCommissionAgreement extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['effective_from' => 'datetime', 'rate_bps' => 'integer'];
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
