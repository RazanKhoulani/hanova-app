<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('qverify_request_id')->nullable()->after('otp');
            $table->timestamp('qverify_expires_at')->nullable()->after('qverify_request_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['qverify_request_id', 'qverify_expires_at']);
        });
    }
};
