<?php

namespace Tests\Unit;

use App\Support\SyrianPhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SyrianPhoneNumberTest extends TestCase
{
    #[DataProvider('phoneFormats')]
    public function test_it_normalizes_supported_syrian_mobile_formats(string $input): void
    {
        $this->assertSame('963945345844', SyrianPhoneNumber::normalize($input));
        $this->assertTrue(SyrianPhoneNumber::isValid($input));
    }

    public static function phoneFormats(): array
    {
        return [
            'international' => ['+963945345844'],
            'international digits' => ['963945345844'],
            'international access prefix' => ['00963945345844'],
            'local leading zero' => ['0945345844'],
            'local subscriber' => ['945345844'],
            'formatted' => ['+963 945 345 844'],
            'arabic digits' => ['٠٩٤٥٣٤٥٨٤٤'],
        ];
    }

    public function test_it_rejects_non_syrian_mobile_numbers(): void
    {
        $this->assertFalse(SyrianPhoneNumber::isValid('+96170123456'));
        $this->assertFalse(SyrianPhoneNumber::isValid('123'));
    }
}
