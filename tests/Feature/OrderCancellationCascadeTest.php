<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderCancellationCascadeTest extends TestCase
{
    #[Test]
    public function canceling_order_releases_payment_allocations(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $customer = $this->createCustomer($user);

        $order = Order::create([
            'uuid' => Str::uuid(),
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'order_date' => now(),
            'order_status' => OrderStatus::APPROVED,
            'total_products' => 1,
            'sub_total' => 5000,
            'vat' => 0,
            'total' => 5000,
            'invoice_no' => 'INV-CANCEL-001',
            'payment_type' => 'HandCash',
            'pay' => 0,
            'due' => 5000,
            'stock_affected' => false,
        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'date' => now(),
            'nature' => 'CHQ-CANCEL-001',
            'payment_type' => 'Cheque',
            'echeance' => now()->addMonth(),
            'amount' => 5000,
            'cashed_in' => true,
        ]);

        $order->payments()->attach($payment->id, [
            'allocated_amount' => 5000,
            'user_id' => $user->id,
        ]);
        $order->recalculatePayments();

        $this->assertDatabaseHas('order_payment', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
        ]);

        // Cancel the order
        $order->update(['order_status' => OrderStatus::CANCELED]);

        // Allocation should be deleted
        $this->assertDatabaseMissing('order_payment', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
        ]);

        // Payment should be fully unallocated again
        $payment->refresh();
        $this->assertEquals(5000, $payment->unallocated_amount);

        // Order pay/due should be reset
        $order->refresh();
        $this->assertEquals(0, $order->pay);
        $this->assertEquals(5000, $order->due);
    }
}
