<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('availability_schedule')->nullable();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('doctor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('doctor_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('availability_schedule');
        });
    }
};
