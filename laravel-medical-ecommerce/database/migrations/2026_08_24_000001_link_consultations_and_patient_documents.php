<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->foreignId('appointment_id')
                ->nullable()
                ->after('patient_id')
                ->unique()
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('consultation_id')
                ->nullable()
                ->after('doctor_id')
                ->unique()
                ->constrained()
                ->nullOnDelete();
        });

        Schema::create('patient_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('consultation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('message_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('document_type')->default('attachment');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_documents');

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('consultation_id');
        });

        Schema::table('consultations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('appointment_id');
        });
    }
};
