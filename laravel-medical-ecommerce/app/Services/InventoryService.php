<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Notification;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(private readonly AuditService $auditService)
    {
    }

    public function setStock(Product $product, int $newQuantity, string $reason, int $userId): Product
    {
        return DB::transaction(function () use ($product, $newQuantity, $reason, $userId) {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            $before = (int) $locked->stock_quantity;

            if ($before === $newQuantity) {
                return $locked;
            }

            $locked->update(['stock_quantity' => $newQuantity]);
            InventoryMovement::create([
                'product_id' => $locked->id,
                'user_id' => $userId,
                'type' => 'manual_adjustment',
                'quantity_change' => $newQuantity - $before,
                'stock_before' => $before,
                'stock_after' => $newQuantity,
                'reason' => trim($reason),
            ]);

            $this->syncLowStockAlert($locked, $before);
            $this->auditService->record('inventory.adjusted', $locked, [
                'stock_before' => $before,
                'stock_after' => $newQuantity,
                'reason' => trim($reason),
            ], $userId);

            return $locked->refresh();
        });
    }

    private function syncLowStockAlert(Product $product, int $stockBefore): void
    {
        if ($product->stock_quantity > $product->low_stock_threshold) {
            if ($product->low_stock_notified_at) {
                $product->update(['low_stock_notified_at' => null]);
            }
            return;
        }

        if ($product->low_stock_notified_at) {
            return;
        }

        foreach (User::role('admin')->pluck('id') as $staffId) {
            Notification::create([
                'user_id' => $staffId,
                'title' => 'تنبيه مخزون',
                'body' => "وصل مخزون {$product->name_ar} إلى {$product->stock_quantity} قطعة.",
                'type' => 'low_stock',
                'data' => [
                    'product_id' => $product->id,
                    'stock_before' => $stockBefore,
                    'stock_after' => (int) $product->stock_quantity,
                ],
            ]);
        }

        $product->update(['low_stock_notified_at' => now()]);
    }
}
