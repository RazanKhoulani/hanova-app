<?php

namespace App\Support;

final class SyrianPhoneNumber
{
    public const VALIDATION_REGEX = '/^9639\d{8}$/';

    public static function normalize(?string $value): string
    {
        return PhoneNumber::normalize($value);
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
