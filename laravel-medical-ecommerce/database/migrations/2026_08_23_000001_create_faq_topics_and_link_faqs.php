<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_topics', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_ar');
            $table->string('name_en');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->foreignId('faq_topic_id')
                ->nullable()
                ->after('id')
                ->constrained('faq_topics')
                ->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->after('is_active');
            $table->index(['faq_topic_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropForeign(['faq_topic_id']);
            $table->dropIndex(['faq_topic_id', 'is_active', 'sort_order']);
            $table->dropColumn(['faq_topic_id', 'sort_order']);
        });

        Schema::dropIfExists('faq_topics');
    }
};
