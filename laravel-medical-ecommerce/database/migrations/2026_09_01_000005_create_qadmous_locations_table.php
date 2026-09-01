<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('qadmous_locations', function(Blueprint $table){ $table->id(); $table->string('governorate_ar'); $table->string('governorate_en')->nullable(); $table->string('branch_ar'); $table->string('branch_en')->nullable(); $table->boolean('is_active')->default(true); $table->unsignedInteger('sort_order')->default(0); $table->timestamps(); }); }
 public function down(): void { Schema::dropIfExists('qadmous_locations'); }
};
