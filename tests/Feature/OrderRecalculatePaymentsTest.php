<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderRecalculatePaymentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_recalculate_updates_pay_and_due(): void
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
            'sub_total' => 10000,
            'vat' => 0,
            'total' => 10000,
            'invoice_no' => 'INV-RECALC-001',
            'payment_type' => 'HandCash',
            'pay' => 0,
            'due' => 10000,
        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'date' => now(),
            'nature' => 'CHQ-RECALC-001',
            'payment_type' => 'Cheque',
            'echeance' => now()->addMonth(),
            'amount' => 7000,
            'cashed_in' => true,
        ]);

        $order->payments()->attach($payment->id, [
            'allocated_amount' => 7000,
            'user_id' => $user->id,
        ]);

        $order->recalculatePayments();
        $order->refresh();

        $this->assertEquals(7000, $order->pay);
        $this->assertEquals(3000, $order->due);
    }

    public function test_recalculate_with_no_allocations(): void
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
            'invoice_no' => 'INV-RECALC-002',
            'payment_type' => 'HandCash',
            'pay' => 3000,
            'due' => 2000,
        ]);

        $order->recalculatePayments();
        $order->refresh();

        $this->assertEquals(0, $order->pay);
        $this->assertEquals(5000, $order->due);
    }

    public function test_recalculate_with_multiple_allocations(): void
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
            'sub_total' => 10000,
            'vat' => 0,
            'total' => 10000,
            'invoice_no' => 'INV-RECALC-003',
            'payment_type' => 'HandCash',
            'pay' => 0,
            'due' => 10000,
        ]);

        $payment1 = Payment::create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'date' => now(),
            'nature' => 'CHQ-RECALC-002',
            'payment_type' => 'Cheque',
            'echeance' => now()->addMonth(),
            'amount' => 4000,
            'cashed_in' => true,
        ]);

        $payment2 = Payment::create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'date' => now(),
            'nature' => 'CHQ-RECALC-003',
            'payment_type' => 'HandCash',
            'echeance' => now()->addMonth(),
            'amount' => 3000,
            'cashed_in' => true,
        ]);

        $order->payments()->attach($payment1->id, [
            'allocated_amount' => 4000,
            'user_id' => $user->id,
        ]);
        $order->payments()->attach($payment2->id, [
            'allocated_amount' => 3000,
            'user_id' => $user->id,
        ]);

        $order->recalculatePayments();
        $order->refresh();

        $this->assertEquals(7000, $order->pay);
        $this->assertEquals(3000, $order->due);
    }
}
