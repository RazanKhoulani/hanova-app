<?php

namespace Tests\Feature;

use App\Models\Concern;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_endpoint_returns_the_initial_catalog_in_one_response(): void
    {
        $product = Product::create([
            'name_ar' => 'منتج تجريبي',
            'name_en' => 'Test Product',
            'price' => 12.5,
            'cost' => 5,
        ]);
        $concern = Concern::create([
            'name_ar' => 'حب الشباب',
            'name_en' => 'Acne',
            'slug' => 'acne',
            'is_active' => true,
        ]);
        $product->concerns()->attach($concern);
        Offer::create([
            'title_ar' => 'عرض',
            'title_en' => 'Offer',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'target_segment' => 'all',
            'is_active' => true,
        ]);

        $this->getJson('/api/home', ['Accept-Language' => 'en'])
            ->assertOk()
            ->assertJsonPath('data.products.0.name', 'Test Product')
            ->assertJsonPath('data.categories.0.slug', 'acne')
            ->assertJsonPath('data.active_offer.title', 'Offer');
    }

    public function test_products_reject_an_unbounded_page_size(): void
    {
        $this->getJson('/api/products?per_page=100')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('per_page');
    }

    public function test_product_seeder_does_not_publish_missing_placeholder_images(): void
    {
        Storage::fake('public');

        $this->seed(ProductSeeder::class);

        $product = Product::where('name_en', 'Gentle Medical Cleanser')->firstOrFail();
        $this->assertNull($product->image);

        $product->update(['image' => 'products/uploaded-cleanser.png']);
        $this->seed(ProductSeeder::class);

        $this->assertSame('products/uploaded-cleanser.png', $product->fresh()->image);
    }

    public function test_available_slots_reject_a_past_date(): void
    {
        $date = now()->subDay()->format('Y-m-d');

        $this->getJson("/api/appointments/available-slots?date={$date}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date');
    }

    public function test_chat_returns_the_latest_messages_in_chronological_order(): void
    {
        $patient = User::factory()->create();
        $doctor = User::factory()->create();
        $conversation = Conversation::create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        for ($index = 1; $index <= 55; $index++) {
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $patient->id,
                'body' => "Message {$index}",
            ]);
        }

        Sanctum::actingAs($patient);

        $response = $this->getJson("/api/chat/conversations/{$conversation->id}/messages")
            ->assertOk()
            ->assertJsonCount(50, 'data');

        $this->assertSame('Message 6', $response->json('data.0.text'));
        $this->assertSame('Message 55', $response->json('data.49.text'));
    }
}
