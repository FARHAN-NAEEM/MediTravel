<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class ReferralLedgerEntry extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['occurred_on' => 'date', 'note' => 'encrypted', 'payee_snapshot' => 'encrypted'];
    }

    public function reversal()
    {
        return $this->hasOne(self::class, 'reverses_id');
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Ledger entries are immutable. Record a reversal instead.'));
        static::deleting(fn () => throw new LogicException('Ledger entries cannot be deleted.'));
    }
}
