<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\StockService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // Stock is not affected on creation, only when approved
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('order_status')) {
            $oldStatus = $order->getOriginal('order_status');
            $newStatus = $order->order_status;

            // Order approved - deduct stock
            if ($newStatus == OrderStatus::APPROVED && !$order->stock_affected) {
                try {
                    $this->stockService->deductStockForOrder($order);
                } catch (\Exception $e) {
                    // Log error but don't fail the order update
                    Log::error("Failed to deduct stock for order {$order->id}: " . $e->getMessage());
                }
            }

            // Order canceled - restore stock if it was previously deducted
            if ($newStatus == OrderStatus::CANCELED && $order->stock_affected) {
                try {
                    $this->stockService->restoreStockForOrder($order);
                } catch (\Exception $e) {
                    // Log error but don't fail the order update
                    Log::error("Failed to restore stock for order {$order->id}: " . $e->getMessage());
                }
            }

            // Order changed from approved to pending - restore stock
            if ($oldStatus == OrderStatus::APPROVED && $newStatus == OrderStatus::PENDING && $order->stock_affected) {
                try {
                    $this->stockService->restoreStockForOrder($order);
                } catch (\Exception $e) {
                    Log::error("Failed to restore stock for order {$order->id}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
