<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class RecalculateOrderTotals extends Command
{
    protected $signature = 'orders:recalculate-totals {--order=* : Specific order ID(s) to recalculate}';

    protected $description = 'Recalculate totals for all orders based on their order items';

    public function handle(): int
    {
        $orderIds = $this->option('order');

        $orders = ! empty($orderIds)
            ? Order::whereIn('id', $orderIds)->get()
            : Order::all();

        $this->info("Recalculating totals for {$orders->count()} order(s)...");

        $updatedCount = 0;
        $errors = [];

        foreach ($orders as $order) {
            try {
                $oldTotal = $order->total;
                $details = $order->details;
                $newTotal = $details->sum('total');
                $due = $newTotal - ($order->pay ?? 0);

                $order->update([
                    'total_products' => $details->count(),
                    'sub_total' => $newTotal,
                    'vat' => 0,
                    'total' => $newTotal,
                    'due' => $due,
                ]);

                if ($oldTotal != $newTotal) {
                    $this->info("Order #{$order->id} (Invoice: {$order->invoice_no}): Total updated from {$oldTotal} to {$newTotal}");
                    $updatedCount++;
                }
            } catch (\Exception $e) {
                $errors[] = "Error updating Order #{$order->id}: ".$e->getMessage();
            }
        }

        $this->info("\nSummary:");
        $this->info("Total orders processed: {$orders->count()}");
        $this->info("Orders with updated totals: {$updatedCount}");

        if (! empty($errors)) {
            $this->error("\nErrors encountered:");
            foreach ($errors as $error) {
                $this->error($error);
            }

            return Command::FAILURE;
        }

        $this->info("\nOrder totals recalculated successfully!");

        return Command::SUCCESS;
    }
}
