<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Otp\QVerifyClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;
use Tests\TestCase;

class QVerifyRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_and_verification_are_completed_by_qverify(): void
    {
        $requestId = '8d7f7f59-6e12-4e22-b2a4-0dbf52c5d5fb';
        Config::set('services.qverify.app_name', 'Hannova');

        $this->mock(QVerifyClient::class, function (MockInterface $mock) use ($requestId) {
            $mock->shouldReceive('sendOtp')
                ->once()
                ->withArgs(static fn (...$arguments): bool => $arguments[0] === 'whatsapp'
                    && $arguments[3] === 5
                    && data_get($arguments, '8.app_name') === 'Hannova')
                ->andReturn([
                    'request_id' => $requestId,
                    'status' => 'sent',
                ]);

            $mock->shouldReceive('verifyOtp')
                ->once()
                ->with('12345', $requestId, null, null)
                ->andReturn([
                    'verified' => true,
                    'status' => 'verified',
                ]);
        });

        $this->postJson('/api/auth/register', [
            'name' => 'QVerify Patient',
            'phone' => '+963945345844',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertAccepted()
            ->assertJsonPath('request_id', $requestId)
            ->assertJsonMissingPath('otp_simulated');

        $user = User::where('phone', '963945345844')->firstOrFail();
        $this->assertSame($requestId, $user->qverify_request_id);
        $this->assertNull($user->otp);
        $this->assertNull($user->phone_verified_at);

        $this->postJson('/api/auth/verify-registration-otp', [
            'phone' => '+963945345844',
            'otp' => '12345',
        ])->assertOk()
            ->assertJsonPath('user.phone', '963945345844')
            ->assertJsonStructure(['access_token']);

        $user->refresh();
        $this->assertNotNull($user->phone_verified_at);
        $this->assertNull($user->qverify_request_id);
    }
}
