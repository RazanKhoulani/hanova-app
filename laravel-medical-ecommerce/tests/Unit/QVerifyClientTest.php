<?php

namespace Tests\Unit;

use App\Services\Otp\HttpQVerifyClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QVerifyClientTest extends TestCase
{
    public function test_send_otp_includes_hannova_as_the_template_app_name(): void
    {
        Http::fake([
            'https://verify-api.example.test/api/sdk/v1/sendOTP' => Http::response([
                'request_id' => '8d7f7f59-6e12-4e22-b2a4-0dbf52c5d5fb',
                'status' => 'sent',
                'expires_at' => now()->addMinutes(5)->toIso8601String(),
            ]),
        ]);

        $client = new HttpQVerifyClient('https://verify-api.example.test/api', 'secret-key');
        $result = $client->sendOtp(
            channel: 'whatsapp',
            recipient: '+963945345844',
            purpose: 'signup',
            numberOfDigits: 5,
            otpFormat: 'numeric',
            expirySeconds: 300,
            locale: 'en',
            templateKey: 'verify_otp_app',
            metadata: ['source' => 'Hanova', 'app_name' => 'Hannova'],
            referenceId: '+963945345844',
        );

        Http::assertSent(function (Request $request): bool {
            $payload = $request->data();

            return $request->hasHeader('X-API-Key', 'secret-key')
                && $payload['channel'] === 'whatsapp'
                && $payload['number_of_digits'] === 5
                && $payload['template_key'] === 'verify_otp_app'
                && data_get($payload, 'metadata.app_name') === 'Hannova';
        });

        $this->assertSame('8d7f7f59-6e12-4e22-b2a4-0dbf52c5d5fb', $result['request_id']);
    }

    public function test_verify_otp_uses_the_qverify_request_id(): void
    {
        Http::fake([
            'https://verify-api.example.test/api/sdk/v1/verifyOTP' => Http::response([
                'verified' => true,
                'status' => 'verified',
            ]),
        ]);

        $client = new HttpQVerifyClient('https://verify-api.example.test/api', 'secret-key');
        $result = $client->verifyOtp(
            otp: '12345',
            requestId: '8d7f7f59-6e12-4e22-b2a4-0dbf52c5d5fb',
        );

        Http::assertSent(function (Request $request): bool {
            $payload = $request->data();

            return $payload['request_id'] === '8d7f7f59-6e12-4e22-b2a4-0dbf52c5d5fb'
                && $payload['otp'] === '12345';
        });
        $this->assertTrue($result['verified']);
    }
}
