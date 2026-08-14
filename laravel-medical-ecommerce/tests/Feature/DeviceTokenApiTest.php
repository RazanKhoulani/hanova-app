<?php

namespace Tests\Feature;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeviceTokenApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_register_and_remove_a_device_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/device-tokens', [
            'token' => 'test-fcm-token',
            'platform' => 'android',
        ])->assertOk();

        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $user->id,
            'token' => 'test-fcm-token',
            'platform' => 'android',
        ]);

        $this->deleteJson('/api/device-tokens', [
            'token' => 'test-fcm-token',
        ])->assertOk();

        $this->assertDatabaseMissing('device_tokens', [
            'token' => 'test-fcm-token',
        ]);
    }

    public function test_a_refreshed_token_is_reassigned_to_the_current_user(): void
    {
        $oldUser = User::factory()->create();
        $newUser = User::factory()->create();
        DeviceToken::create([
            'user_id' => $oldUser->id,
            'token' => 'shared-device-token',
            'platform' => 'android',
        ]);

        Sanctum::actingAs($newUser);

        $this->postJson('/api/device-tokens', [
            'token' => 'shared-device-token',
            'platform' => 'android',
        ])->assertOk();

        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $newUser->id,
            'token' => 'shared-device-token',
        ]);
    }
}
