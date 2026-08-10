<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_conversation_id')->constrained()->cascadeOnDelete();
            $table->enum('sender', ['user', 'bot']);
            $table->text('body');
            $table->json('options')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['bot_conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_messages');
    }
};
