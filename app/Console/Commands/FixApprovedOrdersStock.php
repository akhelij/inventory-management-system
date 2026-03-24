<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixApprovedOrdersStock extends Command
{
    protected $signature = 'orders:fix-stock
        {--order=* : Specific order ID(s) to fix}
        {--dry-run : Preview changes without applying them}';

    protected $description = 'Fix approved orders that have stock_affected=0 by deducting stock and creating activity logs';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $orderIds = $this->option('order');

        $query = Order::where('order_status', OrderStatus::APPROVED)
            ->where('stock_affected', false);

        if (! empty($orderIds)) {
            $query->whereIn('id', $orderIds);
        }

        $orders = $query->with('details')->get();

        if ($orders->isEmpty()) {
            $this->info('No approved orders with stock_affected=0 found.');

            return Command::SUCCESS;
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '')."Found {$orders->count()} order(s) to fix.");
        $this->newLine();

        $fixed = 0;
        $errors = [];

        foreach ($orders as $order) {
            $this->info("Order #{$order->id} (Invoice: {$order->invoice_no}) — {$order->details->count()} items");

            $rows = [];
            $hasIssue = false;

            foreach ($order->details as $item) {
                $product = Product::find($item->product_id);

                if (! $product) {
                    $rows[] = [$item->product_id, '<fg=red>DELETED</>', '-', $item->quantity, '-', '-'];
                    $hasIssue = true;

                    continue;
                }

                $newQty = $product->quantity - $item->quantity;
                $status = $newQty >= 0 ? '<fg=green>OK</>' : '<fg=yellow>NEGATIVE ('.$newQty.')</>';

                $rows[] = [
                    $product->id,
                    $product->name,
                    $product->quantity,
                    $item->quantity,
                    $newQty,
                    $status,
                ];
            }

            $this->table(
                ['Product ID', 'Name', 'Current Stock', 'Order Qty', 'New Stock', 'Status'],
                $rows,
            );

            if ($dryRun) {
                $this->newLine();

                continue;
            }

            if ($hasIssue) {
                $errors[] = "Order #{$order->id}: skipped — contains deleted products";
                $this->warn("  Skipped: contains deleted products.");
                $this->newLine();

                continue;
            }

            if (! $this->confirm("  Apply stock deduction for order #{$order->id}?")) {
                $this->warn("  Skipped by user.");
                $this->newLine();

                continue;
            }

            try {
                DB::transaction(function () use ($order): void {
                    foreach ($order->details as $item) {
                        $product = Product::findOrFail($item->product_id);
                        $newQuantity = $product->quantity - $item->quantity;

                        $product->update(['quantity' => $newQuantity]);

                        StockMovement::create([
                            'product_id' => $product->id,
                            'order_id' => $order->id,
                            'movement_type' => 'deducted',
                            'quantity' => $item->quantity,
                            'balance_after' => $newQuantity,
                            'reason' => "Order #{$order->invoice_no} approved (retroactive fix)",
                            'user_id' => $order->user_id,
                        ]);
                    }

                    $order->update(['stock_affected' => true]);
                });

                $this->info("  Fixed successfully.");
                $fixed++;
            } catch (\Exception $e) {
                $errors[] = "Order #{$order->id}: {$e->getMessage()}";
                $this->error("  Failed: {$e->getMessage()}");
            }

            $this->newLine();
        }

        $this->newLine();
        $this->info('Summary:');
        $this->info("  Orders found: {$orders->count()}");
        $this->info("  Orders fixed: {$fixed}");

        if (! empty($errors)) {
            $this->error('Errors:');
            foreach ($errors as $error) {
                $this->error("  {$error}");
            }

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
