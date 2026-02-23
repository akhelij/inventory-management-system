<?php

use App\Enums\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Reset all orders with order_status=2 (set by the broken PaymentObserver auto-cascade).
        // These were APPROVED orders that got mangled. Reset to APPROVED with pay=0, due=total.
        DB::table('orders')
            ->where('order_status', 2)
            ->update([
                'order_status' => OrderStatus::APPROVED,
                'pay' => 0,
                'due' => DB::raw('`total`'),
            ]);
    }

    public function down(): void
    {
        // Irreversible — we cannot know which orders had status=2
    }
};
