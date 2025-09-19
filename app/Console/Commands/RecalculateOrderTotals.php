<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderDetails;
use Illuminate\Console\Command;

class RecalculateOrderTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:recalculate-totals {--order=* : Specific order ID(s) to recalculate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate totals for all orders based on their order items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderIds = $this->option('order');
        
        if (!empty($orderIds)) {
            $orders = Order::whereIn('id', $orderIds)->get();
            $this->info("Recalculating totals for " . count($orders) . " specific order(s)...");
        } else {
            $orders = Order::all();
            $this->info("Recalculating totals for all " . count($orders) . " orders...");
        }
        
        $updatedCount = 0;
        $errors = [];
        
        foreach ($orders as $order) {
            try {
                $oldTotal = $order->total;
                
                // Get all order details
                $details = OrderDetails::where('order_id', $order->id)->get();
                
                // Calculate new total
                $newTotal = 0;
                foreach ($details as $item) {
                    $newTotal += $item->total;
                }
                
                // Calculate due amount considering existing payments
                $due = $newTotal - ($order->pay ?? 0);
                
                // Update order
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
                $errors[] = "Error updating Order #{$order->id}: " . $e->getMessage();
            }
        }
        
        $this->info("\nSummary:");
        $this->info("Total orders processed: " . count($orders));
        $this->info("Orders with updated totals: {$updatedCount}");
        
        if (!empty($errors)) {
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
