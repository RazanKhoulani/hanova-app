<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeploymentBootstrapTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_database_is_seeded_only_once(): void
    {
        Storage::fake('public');

        $this->artisan('hanova:bootstrap')->assertSuccessful();
        $product = Product::firstOrFail();
        $count = Product::count();
        $product->update(['price' => 321, 'stock_quantity' => 3]);

        $this->artisan('hanova:bootstrap')->assertSuccessful();

        $this->assertSame($count, Product::count());
        $this->assertSame(321.0, (float) $product->fresh()->price);
        $this->assertSame(3, $product->fresh()->stock_quantity);
        $this->assertSame('1', AppSetting::where('key', 'deployment_initialized')->value('value'));
    }

    public function test_redeploy_preserves_existing_catalog_and_users(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $product = Product::create([
            'name_ar' => 'Custom', 'name_en' => 'Gentle Medical Cleanser',
            'price' => 123, 'cost' => 20, 'stock_quantity' => 7,
            'image' => 'products/custom.png',
        ]);
        Storage::disk('public')->put($product->image, 'uploaded');

        $this->artisan('hanova:bootstrap')->assertSuccessful();
        $this->artisan('hanova:bootstrap')->assertSuccessful();

        $this->assertSame(1, Product::count());
        $this->assertSame(1, User::count());
        $this->assertSame(123.0, (float) $product->fresh()->price);
        $this->assertSame(7, $product->fresh()->stock_quantity);
        $this->assertSame($user->password, $user->fresh()->password);
        $this->assertSame('uploaded', Storage::disk('public')->get($product->image));
        $this->assertSame('1', AppSetting::where('key', 'deployment_initialized')->value('value'));
    }
}
