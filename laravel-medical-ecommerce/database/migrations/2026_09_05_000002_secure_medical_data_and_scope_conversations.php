<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('care_scope')->default('doctor')->after('doctor_id');
            $table->index(['care_scope', 'doctor_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->string('attachment_disk')->nullable()->after('attachment');
        });

        Schema::table('patient_documents', function (Blueprint $table) {
            $table->string('storage_disk')->nullable()->after('file_path');
        });

        Schema::table('patient_progress_photos', function (Blueprint $table) {
            $table->string('storage_disk')->nullable()->after('after_image');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->string('medical_file_disk')->nullable()->after('medical_file');
            $table->string('progress_images_disk')->nullable()->after('image_after');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->string('provider_type')->default('doctor')->after('doctor_id');
            $table->text('internal_notes')->nullable()->after('status');
            $table->text('cancellation_reason')->nullable()->after('internal_notes');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            $table->foreignId('assigned_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
        });

        Schema::table('consultations', function (Blueprint $table) {
            $table->text('internal_notes')->nullable()->after('notes');
            $table->text('cancellation_reason')->nullable()->after('internal_notes');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 100);
            $table->nullableMorphs('auditable');
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');

        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['internal_notes', 'cancellation_reason', 'cancelled_at']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_by');
            $table->dropColumn(['provider_type', 'internal_notes', 'cancellation_reason', 'cancelled_at']);
        });

        Schema::table('patients', fn (Blueprint $table) => $table->dropColumn(['medical_file_disk', 'progress_images_disk']));
        Schema::table('patient_progress_photos', fn (Blueprint $table) => $table->dropColumn('storage_disk'));
        Schema::table('patient_documents', fn (Blueprint $table) => $table->dropColumn('storage_disk'));
        Schema::table('messages', fn (Blueprint $table) => $table->dropColumn('attachment_disk'));
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['care_scope', 'doctor_id']);
            $table->dropColumn('care_scope');
        });
    }
};
