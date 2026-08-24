<?php

namespace App\Services\Otp;

use App\Support\SyrianPhoneNumber;
use RuntimeException;

class OtpService
{
    public function __construct(private readonly QVerifyClient $qverify) {}

    /** @return array<string, mixed> */
    public function request(
        string $phone,
        string $purpose,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        $recipient = SyrianPhoneNumber::international($phone);
        $expirySeconds = (int) config('otp.expiry_seconds', 300);
        $codeLength = (int) config('otp.length', 5);

        $result = $this->qverify->sendOtp(
            channel: 'whatsapp',
            recipient: $recipient,
            purpose: $purpose,
            numberOfDigits: $codeLength,
            otpFormat: 'numeric',
            expirySeconds: $expirySeconds,
            locale: (string) config('services.qverify.locale', 'en'),
            templateKey: (string) config('services.qverify.template_key', 'verify_otp_app'),
            metadata: array_filter([
                'source' => config('app.name'),
                // QVerify reads the template service name from metadata.app_name.
                'app_name' => config('services.qverify.app_name', config('app.name')),
                'support_phone' => config('services.support_phone'),
                'purpose' => $purpose,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ], static fn ($value): bool => $value !== null && $value !== ''),
            referenceId: $recipient,
        );

        $requestId = trim((string) ($result['request_id'] ?? ''));
        if ($requestId === '') {
            throw new RuntimeException('QVerify did not return a request identifier.');
        }

        return [
            'request_id' => $requestId,
            'phone' => SyrianPhoneNumber::normalize($phone),
            'delivery_status' => (string) ($result['status'] ?? 'queued'),
            'expires_in' => $expirySeconds,
            'code_length' => $codeLength,
            'expires_at' => $result['expires_at'] ?? now()->addSeconds($expirySeconds)->toIso8601String(),
            'cooldown_seconds' => $result['cooldown_seconds'] ?? null,
        ];
    }

    public function verify(string $phone, string $otp, ?string $requestId): bool
    {
        $result = $this->qverify->verifyOtp(
            otp: $otp,
            requestId: $requestId,
            recipient: $requestId === null ? SyrianPhoneNumber::international($phone) : null,
            channel: $requestId === null ? 'whatsapp' : null,
        );

        return (bool) ($result['verified'] ?? false);
    }
}
