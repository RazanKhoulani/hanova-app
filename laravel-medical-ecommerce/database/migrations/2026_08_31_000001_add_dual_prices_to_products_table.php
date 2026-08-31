<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price_syp', 15, 2)->nullable()->after('price');
            $table->decimal('price_usd', 12, 2)->nullable()->after('price_syp');
            $table->decimal('cost_syp', 15, 2)->nullable()->after('cost');
            $table->decimal('cost_usd', 12, 2)->nullable()->after('cost_syp');
        });

        DB::table('products')->update([
            'price_syp' => DB::raw('price'),
            'cost_syp' => DB::raw('cost'),
        ]);

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('price_usd', 12, 2)->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price_syp', 'price_usd', 'cost_syp', 'cost_usd']);
        });
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn('price_usd'));
    }
};
