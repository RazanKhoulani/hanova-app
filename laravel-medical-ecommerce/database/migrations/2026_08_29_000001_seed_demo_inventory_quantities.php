<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')
            || ! Schema::hasColumn('products', 'stock_quantity')
            || ! Schema::hasColumn('products', 'track_inventory')) {
            return;
        }

        // Keep a small, visible stock mix for the first catalog review.
        $quantities = [
            'Gentle Medical Cleanser' => [12, 3],
            'Medical Moisturizer' => [7, 3],
            'Sunscreen SPF 50' => [0, 2],
            'Niacinamide & Arbutin Serum' => [2, 3],
            'Azelaic Acid Cream' => [5, 2],
        ];

        foreach ($quantities as $name => [$quantity, $threshold]) {
            DB::table('products')
                ->where('name_en', $name)
                ->where(function ($query) {
                    $query->whereNull('catalog_type')->orWhere('catalog_type', 'product');
                })
                ->update([
                    'track_inventory' => true,
                    'stock_quantity' => $quantity,
                    'low_stock_threshold' => $threshold,
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        DB::table('products')
            ->whereIn('name_en', [
                'Gentle Medical Cleanser',
                'Medical Moisturizer',
                'Sunscreen SPF 50',
                'Niacinamide & Arbutin Serum',
                'Azelaic Acid Cream',
            ])
            ->update([
                'stock_quantity' => 0,
                'low_stock_threshold' => 0,
            ]);
    }
};
