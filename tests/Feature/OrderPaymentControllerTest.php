<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderPaymentControllerTest extends TestCase
{
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
            'invoice_no' => 'INV-CTRL-'.Str::random(4),
            'payment_type' => 'HandCash',
            'pay' => 0,
            'due' => 10000,
        ], $overrides['order'] ?? []));

        $payment = Payment::create(array_merge([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'date' => now(),
            'nature' => 'CHQ-CTRL-'.Str::random(4),
            'payment_type' => 'Cheque',
            'echeance' => now()->addMonth(),
            'amount' => 7000,
            'cashed_in' => true,
            'cashed_in_at' => now(),
        ], $overrides['payment'] ?? []));

        return [$user, $customer, $order, $payment];
    }

    private function createOrder(int $userId, int $customerId, array $overrides = []): Order
    {
        return Order::create(array_merge([
            'uuid' => Str::uuid(),
            'user_id' => $userId,
            'customer_id' => $customerId,
            'order_date' => now(),
            'order_status' => OrderStatus::APPROVED,
            'total_products' => 1,
            'sub_total' => 5000,
            'vat' => 0,
            'total' => 5000,
            'invoice_no' => 'INV-CTRL-'.Str::random(4),
            'payment_type' => 'HandCash',
            'pay' => 0,
            'due' => 5000,
        ], $overrides));
    }

    #[Test]
    public function store_allocates_payment_to_order(): void
    {
        [$user, $customer, $order, $payment] = $this->createTestData();

        $response = $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertOk();

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

    #[Test]
    public function store_caps_at_order_due(): void
    {
        [, , $order, $payment] = $this->createTestData([
            'order' => ['total' => 3000, 'sub_total' => 3000, 'due' => 3000],
            'payment' => ['amount' => 7000],
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertOk();

        $this->assertDatabaseHas('order_payment', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'allocated_amount' => 3000,
        ]);
    }

    #[Test]
    public function store_caps_at_payment_unallocated(): void
    {
        [$user, $customer, $order, $payment] = $this->createTestData([
            'payment' => ['amount' => 7000],
        ]);

        $otherOrder = $this->createOrder($user->id, $customer->id);

        $payment->orders()->attach($otherOrder->id, [
            'allocated_amount' => 5000,
            'user_id' => $user->id,
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertOk();

        $this->assertDatabaseHas('order_payment', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'allocated_amount' => 2000,
        ]);
    }

    #[Test]
    public function store_rejects_uncashed_payment(): void
    {
        [, , $order, $payment] = $this->createTestData([
            'payment' => ['cashed_in' => false, 'cashed_in_at' => null],
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertUnprocessable();
        $this->assertDatabaseMissing('order_payment', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
        ]);
    }

    #[Test]
    public function store_rejects_different_customer(): void
    {
        [$user, , $order] = $this->createTestData();

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

        $response->assertUnprocessable();
    }

    #[Test]
    public function store_rejects_pending_order(): void
    {
        [, , $order, $payment] = $this->createTestData([
            'order' => ['order_status' => OrderStatus::PENDING],
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertUnprocessable();
    }

    #[Test]
    public function store_rejects_fully_paid_order(): void
    {
        [, , $order, $payment] = $this->createTestData([
            'order' => ['total' => 5000, 'sub_total' => 5000, 'pay' => 5000, 'due' => 0],
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertUnprocessable();
    }

    #[Test]
    public function store_rejects_duplicate_allocation(): void
    {
        [, , $order, $payment] = $this->createTestData();

        $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}")
            ->assertOk();

        $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}")
            ->assertUnprocessable();
    }

    #[Test]
    public function store_rejects_fully_allocated_payment(): void
    {
        [$user, $customer, $order, $payment] = $this->createTestData([
            'payment' => ['amount' => 5000],
        ]);

        $otherOrder = $this->createOrder($user->id, $customer->id, [
            'invoice_no' => 'INV-CTRL-FULL',
        ]);

        $payment->orders()->attach($otherOrder->id, [
            'allocated_amount' => 5000,
            'user_id' => $user->id,
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertUnprocessable();
    }

    #[Test]
    public function destroy_detaches_payment_from_order(): void
    {
        [$user, , $order, $payment] = $this->createTestData();

        $order->payments()->attach($payment->id, [
            'allocated_amount' => 7000,
            'user_id' => $user->id,
        ]);
        $order->recalculatePayments();

        $response = $this->deleteJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('order_payment', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
        ]);

        $order->refresh();
        $this->assertEquals(0, $order->pay);
        $this->assertEquals(10000, $order->due);
    }

    #[Test]
    public function destroy_returns_not_found_for_nonexistent_allocation(): void
    {
        [, , $order, $payment] = $this->createTestData();

        $response = $this->deleteJson("/api/orders/{$order->id}/payments/{$payment->id}");

        $response->assertNotFound();
    }
}
