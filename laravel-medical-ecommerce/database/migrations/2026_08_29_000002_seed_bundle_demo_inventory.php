<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'stock_quantity')) {
            return;
        }

        // Leave one shared component available so bundle states are visible in the demo.
        DB::table('products')
            ->where('name_en', 'Sunscreen SPF 50')
            ->where(function ($query) {
                $query->whereNull('catalog_type')->orWhere('catalog_type', 'product');
            })
            ->where('stock_quantity', 0)
            ->update([
                'track_inventory' => true,
                'stock_quantity' => 8,
                'low_stock_threshold' => 2,
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        DB::table('products')
            ->where('name_en', 'Sunscreen SPF 50')
            ->where('stock_quantity', 8)
            ->update([
                'stock_quantity' => 0,
                'low_stock_threshold' => 2,
            ]);
    }
};
