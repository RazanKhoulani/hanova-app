<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('orders', function (Blueprint $table) {
        $table->string('qadmous_governorate')->nullable();
        $table->string('qadmous_branch')->nullable();
        $table->string('recipient_name')->nullable();
        $table->string('recipient_phone')->nullable();
        $table->string('tracking_number')->nullable();
    }); }
    public function down(): void { Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['qadmous_governorate','qadmous_branch','recipient_name','recipient_phone','tracking_number'])); }
};
