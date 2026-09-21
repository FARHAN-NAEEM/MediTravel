<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralPatient extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['phone_hash'];

    protected function casts(): array
    {
        return ['name' => 'encrypted', 'phone' => 'encrypted'];
    }
}
