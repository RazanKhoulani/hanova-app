<?php

namespace App\Services\Otp;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HttpQVerifyClient implements QVerifyClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
    ) {}

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
    ): array {
        $payload = array_filter([
            'channel' => $channel,
            'recipient' => $recipient,
            'purpose' => $purpose,
            'number_of_digits' => $numberOfDigits,
            'otp_format' => $otpFormat,
            'expiry_seconds' => $expirySeconds,
            'template_key' => $templateKey,
            'locale' => $locale,
            'metadata' => $metadata !== [] ? $metadata : null,
            'reference_id' => $referenceId,
        ], static fn ($value): bool => $value !== null);

        $response = $this->pendingRequest()
            ->post($this->endpoint('/sdk/v1/sendOTP'), $payload);

        if ($response->successful()) {
            $responseData = $response->json();

            return [
                'request_id' => (string) ($response->json('request_id') ?? ''),
                'status' => (string) ($response->json('status') ?? 'sent'),
                'recipient_masked' => $response->json('recipient_masked'),
                'expires_at' => $response->json('expires_at'),
                'cooldown_seconds' => $response->json('cooldown_seconds'),
                'response' => is_array($responseData) ? $responseData : [],
            ];
        }

        if ($response->status() === 422) {
            $errors = $this->normalizeValidationErrors($response->json('data'));

            throw ValidationException::withMessages($errors !== [] ? $errors : [
                'phone' => [(string) ($response->json('message') ?? 'OTP request validation failed.')],
            ]);
        }

        throw new HttpException(
            $response->status(),
            (string) ($response->json('message') ?? 'OTP delivery failed.'),
        );
    }

    public function verifyOtp(
        string $otp,
        ?string $requestId = null,
        ?string $recipient = null,
        ?string $channel = null,
    ): array {
        $payload = array_filter([
            'request_id' => $requestId,
            'recipient' => $requestId === null ? $recipient : null,
            'channel' => $requestId === null ? $channel : null,
            'otp' => $otp,
        ], static fn ($value): bool => $value !== null);

        $response = $this->pendingRequest()
            ->post($this->endpoint('/sdk/v1/verifyOTP'), $payload);

        if ($response->successful()) {
            $responseData = $response->json();

            return [
                'verified' => (bool) $response->json('verified', $response->json('success', false)),
                'status' => $response->json('status'),
                'verified_at' => $response->json('verified_at'),
                'response' => is_array($responseData) ? $responseData : [],
            ];
        }

        if (in_array($response->status(), [400, 404, 422], true)) {
            $responseData = $response->json();

            return [
                'verified' => false,
                'status' => $response->json('status'),
                'verified_at' => $response->json('verified_at'),
                'response' => is_array($responseData) ? $responseData : [],
            ];
        }

        throw new HttpException(
            $response->status(),
            (string) ($response->json('message') ?? 'OTP verification failed.'),
        );
    }

    /** @return array<string, array<int, string>> */
    private function normalizeValidationErrors(mixed $errors): array
    {
        if (! is_array($errors)) {
            return [];
        }

        $normalized = [];

        foreach ($errors as $field => $messages) {
            $messages = is_array($messages) ? $messages : [$messages];
            $normalized[(string) $field] = array_values(array_filter(
                array_map(static fn ($message): string => (string) $message, $messages),
            ));
        }

        return $normalized;
    }

    private function endpoint(string $path): string
    {
        return rtrim($this->baseUrl, '/').$path;
    }

    private function pendingRequest(): PendingRequest
    {
        $request = Http::acceptJson()
            ->asJson()
            ->connectTimeout(10)
            ->timeout(20)
            ->withHeaders(['X-API-Key' => $this->apiKey]);

        return config('services.qverify.verify_ssl', true)
            ? $request
            : $request->withoutVerifying();
    }
}
