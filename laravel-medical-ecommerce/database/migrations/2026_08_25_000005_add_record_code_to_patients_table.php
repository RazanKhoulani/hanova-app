<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('record_code', 24)->nullable()->unique()->after('id');
        });

        DB::table('patients')
            ->whereNull('record_code')
            ->orderBy('id')
            ->select('id')
            ->each(function ($patient) {
                DB::table('patients')
                    ->where('id', $patient->id)
                    ->update(['record_code' => sprintf('HNV-%06d', $patient->id)]);
            });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropUnique(['record_code']);
            $table->dropColumn('record_code');
        });
    }
};
