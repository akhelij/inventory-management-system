<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentComputedAttributesTest extends TestCase
{
    use RefreshDatabase;

    public function test_unallocated_amount_with_no_allocations(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $customer = $this->createCustomer($user);

        $payment = Payment::create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'date' => now(),
            'nature' => 'CHQ-COMP-001',
            'payment_type' => 'Cheque',
            'echeance' => now()->addMonth(),
            'amount' => 5000,
            'cashed_in' => true,
        ]);

        $this->assertEquals(5000, $payment->unallocated_amount);
        $this->assertFalse($payment->is_fully_allocated);
    }

    public function test_unallocated_amount_with_partial_allocation(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $customer = $this->createCustomer($user);

        $payment = Payment::create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'date' => now(),
            'nature' => 'CHQ-COMP-002',
            'payment_type' => 'Cheque',
            'echeance' => now()->addMonth(),
            'amount' => 5000,
            'cashed_in' => true,
        ]);

        $order = Order::create([
            'uuid' => Str::uuid(),
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'order_date' => now(),
            'order_status' => OrderStatus::APPROVED,
            'total_products' => 1,
            'sub_total' => 3000,
            'vat' => 0,
            'total' => 3000,
            'invoice_no' => 'INV-COMP-001',
            'payment_type' => 'HandCash',
            'pay' => 0,
            'due' => 3000,
        ]);

        $payment->orders()->attach($order->id, [
            'allocated_amount' => 3000,
            'user_id' => $user->id,
        ]);

        $payment->refresh();

        $this->assertEquals(2000, $payment->unallocated_amount);
        $this->assertFalse($payment->is_fully_allocated);
    }

    public function test_is_fully_allocated_when_all_allocated(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $customer = $this->createCustomer($user);

        $payment = Payment::create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'date' => now(),
            'nature' => 'CHQ-COMP-003',
            'payment_type' => 'Cheque',
            'echeance' => now()->addMonth(),
            'amount' => 5000,
            'cashed_in' => true,
        ]);

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
            'invoice_no' => 'INV-COMP-002',
            'payment_type' => 'HandCash',
            'pay' => 0,
            'due' => 5000,
        ]);

        $payment->orders()->attach($order->id, [
            'allocated_amount' => 5000,
            'user_id' => $user->id,
        ]);

        $payment->refresh();

        $this->assertEquals(0, $payment->unallocated_amount);
        $this->assertTrue($payment->is_fully_allocated);
    }
}
