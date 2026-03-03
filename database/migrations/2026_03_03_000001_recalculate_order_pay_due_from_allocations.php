<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_payment')) {
            return;
        }

        // Recalculate pay/due for all orders based solely on order_payment pivot allocations
        $orders = DB::table('orders')->get(['id', 'total']);

        foreach ($orders as $order) {
            $totalAllocated = (float) DB::table('order_payment')
                ->where('order_id', $order->id)
                ->sum('allocated_amount');

            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'pay' => $totalAllocated,
                    'due' => $order->total - $totalAllocated,
                ]);
        }
    }

    public function down(): void
    {
        // Cannot reverse — old pay/due values are lost
    }
};
