<?php

namespace App\Support;

final class Money
{
    public static function toCents(string|int|float $amount): int
    {
        $str = trim((string) $amount);
        if ($str === '') {
            return 0;
        }

        $negative = false;
        if (str_starts_with($str, '-')) {
            $negative = true;
            $str = substr($str, 1);
        }

        $str = str_replace(',', '.', $str);
        [$whole, $dec] = array_pad(explode('.', $str, 2), 2, '');

        $whole = preg_replace('/\D/', '', $whole) ?: '0';
        $dec = preg_replace('/\D/', '', $dec);
        $dec = substr(str_pad($dec, 2, '0'), 0, 2);

        $cents = ((int) $whole) * 100 + (int) $dec;

        return $negative ? -$cents : $cents;
    }

    public static function fromCents(int $cents): string
    {
        $negative = $cents < 0;
        $cents = abs($cents);

        $whole = intdiv($cents, 100);
        $dec = $cents % 100;

        $value = $whole.'.'.str_pad((string) $dec, 2, '0', STR_PAD_LEFT);

        return $negative ? '-'.$value : $value;
    }
}
