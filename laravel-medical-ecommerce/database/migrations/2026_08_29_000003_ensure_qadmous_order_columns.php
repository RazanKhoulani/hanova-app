<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            foreach ([
                'qadmous_governorate',
                'qadmous_branch',
                'recipient_name',
                'recipient_phone',
                'tracking_number',
            ] as $column) {
                if (! Schema::hasColumn('orders', $column)) {
                    $table->string($column)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        // The columns may have been created by the original migration, so do
        // not remove them when rolling back this repair migration.
    }
};
