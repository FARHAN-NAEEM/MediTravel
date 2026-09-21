<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

final class ReferralMoney
{
    public static function minor(string $value, string $field = 'amount'): int
    {
        if (! preg_match('/^\d{1,9}(?:\.\d{1,2})?$/D', $value)) {
            throw ValidationException::withMessages([$field => 'সঠিক অঙ্ক লিখুন (সর্বোচ্চ দুই দশমিক)।']);
        }
        $parts = explode('.', $value);

        return ((int) $parts[0] * 100) + (int) str_pad($parts[1] ?? '', 2, '0');
    }

    public static function bps(string $value): int
    {
        $bps = self::minor($value, 'percentage');
        if ($bps > 10000) {
            throw ValidationException::withMessages(['percentage' => 'কমিশনের হার ০ থেকে ১০০ শতাংশের মধ্যে হতে হবে।']);
        }

        return $bps;
    }

    public static function commission(int $minor, int $bps): int
    {
        return intdiv(max(0, $minor) * $bps + 5000, 10000);
    }

    public static function format(int $minor): string
    {
        return number_format($minor / 100, 2, '.', ',');
    }

    public static function phone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if (preg_match('/^01[3-9]\d{8}$/D', $digits)) {
            $digits = '88'.$digits;
        }
        if (! preg_match('/^\d{8,15}$/D', $digits)) {
            throw ValidationException::withMessages(['phone' => 'দেশের কোডসহ সঠিক ফোন নম্বর দিন।']);
        }

        return '+'.$digits;
    }

    public static function phoneHash(string $phone): string
    {
        return hash_hmac('sha256', self::phone($phone), config('app.key'));
    }
}
