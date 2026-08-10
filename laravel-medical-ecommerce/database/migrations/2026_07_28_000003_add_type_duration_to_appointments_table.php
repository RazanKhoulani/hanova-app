<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('appointment_type')->default('treatment')->after('type');
            $table->unsignedSmallInteger('duration_minutes')->default(30)->after('appointment_type');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['appointment_type', 'duration_minutes']);
        });
    }
};
