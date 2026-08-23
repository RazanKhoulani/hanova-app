<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminChatRealtimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.key' => 'test-key',
            'broadcasting.connections.pusher.secret' => 'test-secret',
            'broadcasting.connections.pusher.app_id' => 'test-app',
            'broadcasting.connections.pusher.options.cluster' => 'mt1',
        ]);

        // The testing environment boots with the null broadcaster. Register the
        // application channels again after switching this test to Pusher.
        require base_path('routes/channels.php');
    }

    public function test_admin_chat_page_subscribes_to_its_private_pusher_channel(): void
    {
        [$admin, $patient, $conversation] = $this->chatParticipants();
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $patient->id,
            'body' => 'Hello',
            'type' => 'text',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.chats.show', $conversation))
            ->assertOk()
            ->assertSee('https://js.pusher.com/8.3.0/pusher.min.js', false)
            ->assertSee("const conversationId = {$conversation->id};", false)
            ->assertSee('private-conversation.${conversationId}', false)
            ->assertSee('admin\/broadcasting\/auth', false);
    }

    public function test_admin_can_authorize_the_private_conversation_channel(): void
    {
        [$admin, , $conversation] = $this->chatParticipants();

        $this->actingAs($admin)
            ->postJson(route('admin.broadcasting.auth'), [
                'socket_id' => '123.456',
                'channel_name' => "private-conversation.{$conversation->id}",
            ])
            ->assertOk()
            ->assertJsonStructure(['auth']);
    }

    public function test_open_admin_chat_can_mark_incoming_messages_as_read(): void
    {
        [$admin, $patient, $conversation] = $this->chatParticipants();
        $incoming = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $patient->id,
            'body' => 'Incoming',
            'type' => 'text',
        ]);
        $outgoing = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $admin->id,
            'body' => 'Outgoing',
            'type' => 'text',
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.chats.read', $conversation))
            ->assertOk()
            ->assertJson(['updated' => 1]);

        $this->assertTrue($incoming->fresh()->is_read);
        $this->assertFalse($outgoing->fresh()->is_read);
    }

    public function test_admin_can_send_a_message_without_reloading_the_chat_page(): void
    {
        Event::fake([MessageSent::class]);
        [$admin, , $conversation] = $this->chatParticipants();

        $this->actingAs($admin)
            ->postJson(route('admin.chats.messages.store', $conversation), [
                'body' => 'Realtime reply',
            ])
            ->assertCreated()
            ->assertJsonPath('data.conversation_id', $conversation->id)
            ->assertJsonPath('data.sender_id', $admin->id)
            ->assertJsonPath('data.message', 'Realtime reply');

        Event::assertDispatched(MessageSent::class);
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $admin->id,
            'body' => 'Realtime reply',
        ]);
    }

    /** @return array{User, User, Conversation} */
    private function chatParticipants(): array
    {
        $admin = User::factory()->create();
        $patient = User::factory()->create();
        $admin->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));

        $conversation = Conversation::create([
            'user_id' => $patient->id,
            'doctor_id' => $admin->id,
        ]);

        return [$admin, $patient, $conversation];
    }
}
