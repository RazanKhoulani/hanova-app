<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_method')->default('home_delivery')->after('payment_method');
            $table->string('pickup_location')->nullable()->after('delivery_method');
            $table->foreignId('delivery_area_id')
                ->nullable()
                ->after('pickup_location')
                ->constrained('delivery_areas')
                ->nullOnDelete();
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('delivery_area_id');
            $table->foreignId('delivery_user_id')
                ->nullable()
                ->after('delivery_fee')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('payment_status')->default('unpaid')->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_user_id');
            $table->dropConstrainedForeignId('delivery_area_id');
            $table->dropColumn([
                'delivery_method',
                'pickup_location',
                'delivery_fee',
                'payment_status',
            ]);
        });
    }
};
