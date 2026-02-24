<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderPaymentRelationshipTest extends TestCase
{
    private function createOrderAndPayment(): array
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
            'invoice_no' => 'INV-REL-001',
            'payment_type' => 'HandCash',
            'pay' => 0,
            'due' => 5000,
        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'date' => now(),
            'nature' => 'CHQ-REL-001',
            'payment_type' => 'Cheque',
            'echeance' => now()->addMonth(),
            'amount' => 3000,
            'cashed_in' => true,
            'cashed_in_at' => now(),
        ]);

        return [$user, $order, $payment];
    }

    #[Test]
    public function order_has_payments_relationship(): void
    {
        [$user, $order, $payment] = $this->createOrderAndPayment();

        $order->payments()->attach($payment->id, [
            'allocated_amount' => 3000,
            'user_id' => $user->id,
        ]);

        $this->assertCount(1, $order->payments);
        $this->assertEquals($payment->id, $order->payments->first()->id);
        $this->assertEquals(3000, $order->payments->first()->pivot->allocated_amount);
    }

    #[Test]
    public function payment_has_orders_relationship(): void
    {
        [$user, $order, $payment] = $this->createOrderAndPayment();

        $payment->orders()->attach($order->id, [
            'allocated_amount' => 3000,
            'user_id' => $user->id,
        ]);

        $this->assertCount(1, $payment->orders);
        $this->assertEquals($order->id, $payment->orders->first()->id);
        $this->assertEquals(3000, $payment->orders->first()->pivot->allocated_amount);
    }
}
