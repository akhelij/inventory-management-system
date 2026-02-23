<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderPaymentRelationshipTest extends TestCase
{
    use RefreshDatabase;

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

        return [$user, $customer, $order, $payment];
    }

    public function test_order_has_payments_relationship(): void
    {
        [$user, $customer, $order, $payment] = $this->createOrderAndPayment();

        $order->payments()->attach($payment->id, [
            'allocated_amount' => 3000,
            'user_id' => $user->id,
        ]);

        $this->assertCount(1, $order->payments);
        $this->assertEquals($payment->id, $order->payments->first()->id);
        $this->assertEquals(3000, $order->payments->first()->pivot->allocated_amount);
    }

    public function test_payment_has_orders_relationship(): void
    {
        [$user, $customer, $order, $payment] = $this->createOrderAndPayment();

        $payment->orders()->attach($order->id, [
            'allocated_amount' => 3000,
            'user_id' => $user->id,
        ]);

        $this->assertCount(1, $payment->orders);
        $this->assertEquals($order->id, $payment->orders->first()->id);
        $this->assertEquals(3000, $payment->orders->first()->pivot->allocated_amount);
    }
}
