<?php

namespace App\Support;

final class PhoneNumber
{
    /** Stored without a leading plus, using the E.164 8-15 digit envelope. */
    public const VALIDATION_REGEX = '/^[1-9]\d{7,14}$/';

    public static function normalize(?string $value, string $defaultCallingCode = '963'): string
    {
        $raw = strtr(trim((string) $value), [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        $hasInternationalPrefix = str_starts_with($raw, '+') || str_starts_with($raw, '00');
        $digits = preg_replace('/\D+/u', '', $raw) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (! $hasInternationalPrefix && $defaultCallingCode === '963') {
            if (preg_match('/^09\d{8}$/', $digits) === 1) {
                return '963'.substr($digits, 1);
            }

            if (preg_match('/^9\d{8}$/', $digits) === 1) {
                return '963'.$digits;
            }
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
