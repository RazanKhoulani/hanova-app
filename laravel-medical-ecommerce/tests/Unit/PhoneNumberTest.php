<?php

namespace Tests\Unit;

use App\Support\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    #[DataProvider('validNumbers')]
    public function test_it_normalizes_international_and_default_syrian_numbers(string $input, string $expected): void
    {
        $this->assertSame($expected, PhoneNumber::normalize($input));
        $this->assertTrue(PhoneNumber::isValid($input));
        $this->assertSame('+'.$expected, PhoneNumber::international($input));
    }

    public static function validNumbers(): array
    {
        return [
            'Syrian local with zero' => ['0945345844', '963945345844'],
            'Syrian local without zero' => ['945345844', '963945345844'],
            'Syrian international' => ['+963945345844', '963945345844'],
            'US international' => ['+12025550123', '12025550123'],
            'UAE access prefix' => ['00971501234567', '971501234567'],
            'Arabic digits' => ['٠٩٤٥٣٤٥٨٤٤', '963945345844'],
        ];
    }

    public function test_it_rejects_numbers_outside_the_e164_length_envelope(): void
    {
        $this->assertFalse(PhoneNumber::isValid('123'));
        $this->assertFalse(PhoneNumber::isValid('+1234567890123456'));
    }
}
