<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('concern_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concern_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['concern_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concern_product');
    }
};
