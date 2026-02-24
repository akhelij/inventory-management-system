<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderRecalculatePaymentsTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
        $this->actingAs($this->user);
    }

    private function createOrder(array $overrides = []): Order
    {
        $customer = $this->createCustomer($this->user);

        return Order::create(array_merge([
            'uuid' => Str::uuid(),
            'user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'order_date' => now(),
            'order_status' => OrderStatus::APPROVED,
            'total_products' => 1,
            'sub_total' => 10000,
            'vat' => 0,
            'total' => 10000,
            'invoice_no' => 'INV-RECALC-'.Str::random(4),
            'payment_type' => 'HandCash',
            'pay' => 0,
            'due' => 10000,
        ], $overrides));
    }

    private function createPayment(int $customerId, array $overrides = []): Payment
    {
        return Payment::create(array_merge([
            'user_id' => $this->user->id,
            'customer_id' => $customerId,
            'date' => now(),
            'nature' => 'CHQ-RECALC-'.Str::random(4),
            'payment_type' => 'Cheque',
            'echeance' => now()->addMonth(),
            'amount' => 7000,
            'cashed_in' => true,
        ], $overrides));
    }

    #[Test]
    public function recalculate_updates_pay_and_due(): void
    {
        $order = $this->createOrder();
        $payment = $this->createPayment($order->customer_id);

        $order->payments()->attach($payment->id, [
            'allocated_amount' => 7000,
            'user_id' => $this->user->id,
        ]);

        $order->recalculatePayments();
        $order->refresh();

        $this->assertEquals(7000, $order->pay);
        $this->assertEquals(3000, $order->due);
    }

    #[Test]
    public function recalculate_with_no_allocations(): void
    {
        $order = $this->createOrder([
            'sub_total' => 5000,
            'total' => 5000,
            'pay' => 3000,
            'due' => 2000,
        ]);

        $order->recalculatePayments();
        $order->refresh();

        $this->assertEquals(0, $order->pay);
        $this->assertEquals(5000, $order->due);
    }

    #[Test]
    public function recalculate_with_multiple_allocations(): void
    {
        $order = $this->createOrder();

        $payment1 = $this->createPayment($order->customer_id, ['amount' => 4000]);
        $payment2 = $this->createPayment($order->customer_id, [
            'amount' => 3000,
            'payment_type' => 'HandCash',
        ]);

        $order->payments()->attach($payment1->id, [
            'allocated_amount' => 4000,
            'user_id' => $this->user->id,
        ]);
        $order->payments()->attach($payment2->id, [
            'allocated_amount' => 3000,
            'user_id' => $this->user->id,
        ]);

        $order->recalculatePayments();
        $order->refresh();

        $this->assertEquals(7000, $order->pay);
        $this->assertEquals(3000, $order->due);
    }
}
