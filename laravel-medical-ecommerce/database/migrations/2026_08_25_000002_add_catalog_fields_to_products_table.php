<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('category')->index();
            $table->string('catalog_type')->default('product')->after('brand')->index();
            $table->json('bundle_product_ids')->nullable()->after('catalog_type');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['brand']);
            $table->dropIndex(['catalog_type']);
            $table->dropColumn(['brand', 'catalog_type', 'bundle_product_ids']);
        });
    }
};
