<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderStatusCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleanup_logic_resets_status_two_orders(): void
    {
        $user = $this->createUser();
        $customer = $this->createCustomer($user);

        // Create an order with status=2 (the broken state from old PaymentObserver)
        $order = Order::create([
            'uuid' => Str::uuid(),
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'order_date' => now(),
            'order_status' => 2,
            'total_products' => 1,
            'sub_total' => 1000,
            'vat' => 0,
            'total' => 1000,
            'invoice_no' => 'INV-TEST-001',
            'payment_type' => 'HandCash',
            'pay' => 1000,
            'due' => 0,
        ]);

        // Verify it's broken
        $this->assertEquals(2, $order->order_status);

        // Apply the fix (same logic as the migration)
        DB::table('orders')
            ->where('order_status', 2)
            ->update([
                'order_status' => OrderStatus::APPROVED,
                'pay' => 0,
                'due' => DB::raw('`total`'),
            ]);

        $order->refresh();
        $this->assertEquals(OrderStatus::APPROVED, $order->order_status);
        $this->assertEquals(0, $order->pay);
        $this->assertEquals(1000, $order->due);
    }
}
