<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('products', function (Blueprint $table) {
            foreach (['usage', 'suitable_for', 'active_ingredients', 'warnings'] as $field) {
                $table->text($field.'_ar')->nullable();
                $table->text($field.'_en')->nullable();
            }
        });
    }
    public function down(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['usage_ar','usage_en','suitable_for_ar','suitable_for_en','active_ingredients_ar','active_ingredients_en','warnings_ar','warnings_en']);
        });
    }
};
