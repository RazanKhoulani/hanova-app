<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('track_inventory')->default(true)->after('bundle_product_ids');
            $table->unsignedInteger('stock_quantity')->default(0)->after('track_inventory');
            $table->unsignedInteger('low_stock_threshold')->default(5)->after('stock_quantity');
            $table->index(['track_inventory', 'stock_quantity']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('inventory_reserved_at')->nullable()->after('status');
            $table->timestamp('inventory_released_at')->nullable()->after('inventory_reserved_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['inventory_reserved_at', 'inventory_released_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['track_inventory', 'stock_quantity']);
            $table->dropColumn(['track_inventory', 'stock_quantity', 'low_stock_threshold']);
        });
    }
};
