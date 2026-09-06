<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_areas', function (Blueprint $table) {
            $table->decimal('fee_usd', 12, 2)->nullable()->after('fee');
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->decimal('discount_value_usd', 12, 2)->nullable()->after('discount_value');
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->decimal('discount_value_usd', 12, 2)->nullable()->after('discount_value');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal_amount', 15, 2)->default(0)->after('total_amount');
            $table->decimal('subtotal_usd', 12, 2)->nullable()->after('subtotal_amount');
            $table->decimal('discount_amount_usd', 12, 2)->nullable()->after('discount_amount');
            $table->decimal('delivery_fee_usd', 12, 2)->nullable()->after('delivery_fee');
            $table->decimal('total_amount_usd', 12, 2)->nullable()->after('total_amount');
            $table->string('payment_receipt_status')->default('not_required')->after('shipping_receipt');
            $table->string('receipt_disk')->nullable()->after('payment_receipt_status');
            $table->foreignId('receipt_reviewed_by')
                ->nullable()
                ->after('receipt_disk')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('receipt_reviewed_at')->nullable()->after('receipt_reviewed_by');
            $table->text('receipt_rejection_reason')->nullable()->after('receipt_reviewed_at');
        });

        DB::table('orders')->update([
            'subtotal_amount' => DB::raw('total_amount - COALESCE(delivery_fee, 0) + COALESCE(discount_amount, 0)'),
        ]);

        DB::table('orders')
            ->whereIn('payment_method', ['cash', 'cash_on_delivery'])
            ->update(['payment_receipt_status' => 'not_required']);

        DB::table('orders')
            ->whereNotIn('payment_method', ['cash', 'cash_on_delivery'])
            ->where('payment_status', 'paid')
            ->update(['payment_receipt_status' => 'approved']);

        DB::table('orders')
            ->whereNotIn('payment_method', ['cash', 'cash_on_delivery'])
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('shipping_receipt')
            ->update(['payment_receipt_status' => 'pending']);

        DB::table('orders')
            ->whereNotIn('payment_method', ['cash', 'cash_on_delivery'])
            ->where('payment_status', '!=', 'paid')
            ->whereNull('shipping_receipt')
            ->update(['payment_receipt_status' => 'missing']);

        DB::table('app_settings')->whereIn('key', [
            'display_currency',
            'syp_old_per_new',
            'syp_old_per_usd',
            'show_dual_syp',
        ])->delete();
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('receipt_reviewed_by');
            $table->dropColumn([
                'subtotal_amount',
                'subtotal_usd',
                'discount_amount_usd',
                'delivery_fee_usd',
                'total_amount_usd',
                'payment_receipt_status',
                'receipt_disk',
                'receipt_reviewed_at',
                'receipt_rejection_reason',
            ]);
        });

        Schema::table('coupons', fn (Blueprint $table) => $table->dropColumn('discount_value_usd'));
        Schema::table('offers', fn (Blueprint $table) => $table->dropColumn('discount_value_usd'));
        Schema::table('delivery_areas', fn (Blueprint $table) => $table->dropColumn('fee_usd'));
    }
};
