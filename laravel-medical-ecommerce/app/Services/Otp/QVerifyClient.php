<?php

namespace App\Services\Otp;

interface QVerifyClient
{
    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function sendOtp(
        string $channel,
        string $recipient,
        string $purpose,
        int $numberOfDigits,
        string $otpFormat,
        int $expirySeconds,
        string $locale,
        ?string $templateKey = null,
        array $metadata = [],
        ?string $referenceId = null,
    ): array;

    /** @return array<string, mixed> */
    public function verifyOtp(
        string $otp,
        ?string $requestId = null,
        ?string $recipient = null,
        ?string $channel = null,
    ): array;
}
