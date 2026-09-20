<?php

namespace App\Support;

class WhatsappNumber
{
    public static function normalize(string $value): string
    {
        $number = preg_replace('/\D+/', '', $value) ?? '';

        // Local Bangladesh mobile numbers need the country code for click-to-chat.
        return preg_match('/^01[3-9]\d{8}$/', $number) ? '88'.$number : $number;
    }
}
