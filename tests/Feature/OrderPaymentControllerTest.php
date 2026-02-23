<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderPaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createTestData(array $overrides = []): array
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $customer = $this->createCustomer($user);

        $order = Order::create(array_merge([
            'uuid' => Str::uuid(),
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'order_date' => now(),
            'order_status' => OrderStatus::APPROVED,
            'total_products' => 1,
            'sub_total' => 10000,
            'vat' => 0,
            'total' => 10000,
            'invoice_no' => 'INV-CTRL-' . Str::random(4),
            'payment_type' => 'HandCash',
            'pay' => 0,
            'due' => 10000,
        ], $overrides['order'] ?? []));

        $payment = Payment::create(array_merge([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'date' => now(),
            'nature' => 'CHQ-CTRL-' . Str::random(4),
            'payment_type' => 'Cheque',
            'echeance' => now()->addMonth(),
            'amount' => 7000,
            'cashed_in' => true,
            'cashed_in_at' => now(),
        ], $overrides['payment'] ?? []));

        return [$user, $customer, $order, $payment];
    }

    // === STORE TESTS ===

    public function test_store_allocates_payment_to_order(): void
    {
        [$user, $customer, $order, $payment] = $this->createTestData();

        $response = $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('order_payment', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'allocated_amount' => 7000,
            'user_id' => $user->id,
        ]);

        $order->refresh();
        $this->assertEquals(7000, $order->pay);
        $this->assertEquals(3000, $order->due);
    }

    public function test_store_caps_at_order_due(): void
    {
        [$user, $customer, $order, $payment] = $this->createTestData([
            'order' => ['total' => 3000, 'sub_total' => 3000, 'due' => 3000],
            'payment' => ['amount' => 7000],
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('order_payment', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'allocated_amount' => 3000,
        ]);
    }

    public function test_store_caps_at_payment_unallocated(): void
    {
        [$user, $customer, $order, $payment] = $this->createTestData([
            'payment' => ['amount' => 7000],
        ]);

        // Pre-allocate 5000 of the 7000 payment to another order
        $otherOrder = Order::create([
            'uuid' => Str::uuid(),
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'order_date' => now(),
            'order_status' => OrderStatus::APPROVED,
            'total_products' => 1,
            'sub_total' => 5000,
            'vat' => 0,
            'total' => 5000,
            'invoice_no' => 'INV-CTRL-OTHER',
            'payment_type' => 'HandCash',
            'pay' => 0,
            'due' => 5000,
        ]);

        $payment->orders()->attach($otherOrder->id, [
            'allocated_amount' => 5000,
            'user_id' => $user->id,
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('order_payment', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'allocated_amount' => 2000,
        ]);
    }

    public function test_store_rejects_uncashed_payment(): void
    {
        [$user, $customer, $order, $payment] = $this->createTestData([
            'payment' => ['cashed_in' => false, 'cashed_in_at' => null],
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertStatus(422);
        $this->assertDatabaseMissing('order_payment', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
        ]);
    }

    public function test_store_rejects_different_customer(): void
    {
        [$user, $customer, $order, $payment] = $this->createTestData();

        $otherCustomer = Customer::factory()->create(['user_id' => $user->id]);
        $otherPayment = Payment::create([
            'user_id' => $user->id,
            'customer_id' => $otherCustomer->id,
            'date' => now(),
            'nature' => 'CHQ-OTHER-CUST',
            'payment_type' => 'Cheque',
            'echeance' => now()->addMonth(),
            'amount' => 5000,
            'cashed_in' => true,
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/payments/{$otherPayment->id}");

        $response->assertStatus(422);
    }

    public function test_store_rejects_pending_order(): void
    {
        [$user, $customer, $order, $payment] = $this->createTestData([
            'order' => ['order_status' => OrderStatus::PENDING],
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertStatus(422);
    }

    public function test_store_rejects_fully_paid_order(): void
    {
        [$user, $customer, $order, $payment] = $this->createTestData([
            'order' => ['total' => 5000, 'sub_total' => 5000, 'pay' => 5000, 'due' => 0],
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertStatus(422);
    }

    public function test_store_rejects_duplicate_allocation(): void
    {
        [$user, $customer, $order, $payment] = $this->createTestData();

        $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}")
            ->assertStatus(200);

        $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}")
            ->assertStatus(422);
    }

    public function test_store_rejects_fully_allocated_payment(): void
    {
        [$user, $customer, $order, $payment] = $this->createTestData([
            'payment' => ['amount' => 5000],
        ]);

        $otherOrder = Order::create([
            'uuid' => Str::uuid(),
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'order_date' => now(),
            'order_status' => OrderStatus::APPROVED,
            'total_products' => 1,
            'sub_total' => 5000,
            'vat' => 0,
            'total' => 5000,
            'invoice_no' => 'INV-CTRL-FULL',
            'payment_type' => 'HandCash',
            'pay' => 0,
            'due' => 5000,
        ]);
        $payment->orders()->attach($otherOrder->id, [
            'allocated_amount' => 5000,
            'user_id' => $user->id,
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertStatus(422);
    }

    // === DESTROY TESTS ===

    public function test_destroy_detaches_payment_from_order(): void
    {
        [$user, $customer, $order, $payment] = $this->createTestData();

        $order->payments()->attach($payment->id, [
            'allocated_amount' => 7000,
            'user_id' => $user->id,
        ]);
        $order->recalculatePayments();

        $response = $this->deleteJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('order_payment', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
        ]);

        $order->refresh();
        $this->assertEquals(0, $order->pay);
        $this->assertEquals(10000, $order->due);
    }

    public function test_destroy_returns_404_for_nonexistent_allocation(): void
    {
        [$user, $customer, $order, $payment] = $this->createTestData();

        $response = $this->deleteJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertStatus(404);
    }
}
