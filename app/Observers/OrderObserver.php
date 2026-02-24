<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\StockService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function __construct(
        protected StockService $stockService,
    ) {}

    public function updated(Order $order): void
    {
        if (! $order->wasChanged('order_status')) {
            return;
        }

        $oldStatus = $order->getOriginal('order_status');
        $newStatus = $order->order_status;

        try {
            if ($newStatus == OrderStatus::APPROVED && ! $order->stock_affected) {
                $this->stockService->deductStockForOrder($order);
            }

            $shouldRestore = ($newStatus == OrderStatus::CANCELED && $order->stock_affected)
                || ($oldStatus == OrderStatus::APPROVED && $newStatus == OrderStatus::PENDING && $order->stock_affected);

            if ($shouldRestore) {
                $this->stockService->restoreStockForOrder($order);
            }
        } catch (\Exception $e) {
            Log::error("Stock operation failed for order {$order->id}: ".$e->getMessage());
        }
    }
}
