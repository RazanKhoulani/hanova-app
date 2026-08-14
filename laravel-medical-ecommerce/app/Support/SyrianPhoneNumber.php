<?php

namespace App\Support;

final class SyrianPhoneNumber
{
    public const VALIDATION_REGEX = '/^9639\d{8}$/';

    public static function normalize(?string $value): string
    {
        $value = strtr(trim((string) $value), [
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);

        $digits = preg_replace('/\D+/u', '', $value) ?? '';

        if (str_starts_with($digits, '00963')) {
            $digits = substr($digits, 2);
        }

        if (preg_match('/^09\d{8}$/', $digits) === 1) {
            return '963'.substr($digits, 1);
        }

        if (preg_match('/^9\d{8}$/', $digits) === 1) {
            return '963'.$digits;
        }

        return $digits;
    }

    public static function international(?string $value): string
    {
        $normalized = self::normalize($value);

        return $normalized === '' ? '' : '+'.$normalized;
    }

    public static function isValid(?string $value): bool
    {
        return preg_match(self::VALIDATION_REGEX, self::normalize($value)) === 1;
    }
}
