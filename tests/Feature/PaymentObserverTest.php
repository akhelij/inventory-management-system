<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentObserverTest extends TestCase
{
    #[Test]
    public function cashing_in_payment_does_not_modify_orders(): void
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
            'invoice_no' => 'INV-OBS-001',
            'payment_type' => 'HandCash',
            'pay' => 0,
            'due' => 5000,
        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'date' => now(),
            'nature' => 'CHQ-OBS-001',
            'payment_type' => 'Cheque',
            'echeance' => now()->addMonth(),
            'amount' => 5000,
            'cashed_in' => false,
        ]);

        $payment->update([
            'cashed_in' => true,
            'cashed_in_at' => now(),
        ]);

        $order->refresh();
        $this->assertEquals(OrderStatus::APPROVED, $order->order_status);
        $this->assertEquals(0, $order->pay);
        $this->assertEquals(5000, $order->due);
    }
}
