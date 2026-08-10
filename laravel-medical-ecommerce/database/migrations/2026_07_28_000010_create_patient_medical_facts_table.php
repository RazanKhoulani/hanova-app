<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_medical_facts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('source_message_id')->nullable()->constrained('messages')->nullOnDelete();
            $table->string('key');
            $table->text('value');
            $table->decimal('confidence', 4, 2)->default(0.70);
            $table->enum('status', ['suggested', 'confirmed', 'ignored'])->default('suggested');
            $table->timestamps();

            $table->index(['patient_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_medical_facts');
    }
};
