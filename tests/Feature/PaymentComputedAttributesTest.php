<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentComputedAttributesTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
        $this->actingAs($this->user);
    }

    private function createPayment(int $customerId, array $overrides = []): Payment
    {
        return Payment::create(array_merge([
            'user_id' => $this->user->id,
            'customer_id' => $customerId,
            'date' => now(),
            'nature' => 'CHQ-COMP-'.Str::random(4),
            'payment_type' => 'Cheque',
            'echeance' => now()->addMonth(),
            'amount' => 5000,
            'cashed_in' => true,
        ], $overrides));
    }

    private function createOrder(int $customerId, array $overrides = []): Order
    {
        return Order::create(array_merge([
            'uuid' => Str::uuid(),
            'user_id' => $this->user->id,
            'customer_id' => $customerId,
            'order_date' => now(),
            'order_status' => OrderStatus::APPROVED,
            'total_products' => 1,
            'sub_total' => 5000,
            'vat' => 0,
            'total' => 5000,
            'invoice_no' => 'INV-COMP-'.Str::random(4),
            'payment_type' => 'HandCash',
            'pay' => 0,
            'due' => 5000,
        ], $overrides));
    }

    #[Test]
    public function unallocated_amount_with_no_allocations(): void
    {
        $customer = $this->createCustomer($this->user);
        $payment = $this->createPayment($customer->id);

        $this->assertEquals(5000, $payment->unallocated_amount);
        $this->assertFalse($payment->is_fully_allocated);
    }

    #[Test]
    public function unallocated_amount_with_partial_allocation(): void
    {
        $customer = $this->createCustomer($this->user);
        $payment = $this->createPayment($customer->id);
        $order = $this->createOrder($customer->id, [
            'sub_total' => 3000,
            'total' => 3000,
            'due' => 3000,
        ]);

        $payment->orders()->attach($order->id, [
            'allocated_amount' => 3000,
            'user_id' => $this->user->id,
        ]);

        $payment->refresh();

        $this->assertEquals(2000, $payment->unallocated_amount);
        $this->assertFalse($payment->is_fully_allocated);
    }

    #[Test]
    public function is_fully_allocated_when_all_allocated(): void
    {
        $customer = $this->createCustomer($this->user);
        $payment = $this->createPayment($customer->id);
        $order = $this->createOrder($customer->id);

        $payment->orders()->attach($order->id, [
            'allocated_amount' => 5000,
            'user_id' => $this->user->id,
        ]);

        $payment->refresh();

        $this->assertEquals(0, $payment->unallocated_amount);
        $this->assertTrue($payment->is_fully_allocated);
    }
}
