<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    #[Test]
    public function store_returns_json_when_requested(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $customer = $this->createCustomer($user);

        $response = $this->postJson("/payments/{$customer->id}", [
            'customer_id' => $customer->id,
            'nature' => 'CHQ-TEST-001',
            'payment_type' => 'Cheque',
            'bank' => 'CIH',
            'date' => now()->format('d/m/Y'),
            'echeance' => now()->addMonth()->format('d/m/Y'),
            'amount' => 5000,
            'description' => 'Test payment',
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'payment' => ['id', 'nature', 'payment_type', 'date', 'echeance', 'amount', 'cashed_in', 'reported', 'unallocated_amount', 'is_fully_allocated'],
        ]);

        $this->assertDatabaseHas('payments', [
            'nature' => 'CHQ-TEST-001',
            'customer_id' => $customer->id,
        ]);
    }

    #[Test]
    public function store_returns_redirect_for_standard_requests(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $customer = $this->createCustomer($user);

        $response = $this->post("/payments/{$customer->id}", [
            'customer_id' => $customer->id,
            'nature' => 'CHQ-TEST-002',
            'payment_type' => 'Cheque',
            'bank' => 'CIH',
            'date' => now()->format('d/m/Y'),
            'echeance' => now()->addMonth()->format('d/m/Y'),
            'amount' => 3000,
        ]);

        $response->assertRedirect(route('customers.show', $customer->uuid));
    }

    #[Test]
    public function store_returns_validation_errors_as_json(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $customer = $this->createCustomer($user);

        $response = $this->postJson("/payments/{$customer->id}", [
            'customer_id' => $customer->id,
            'nature' => '',
            'payment_type' => 'InvalidType',
            'amount' => '',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['nature', 'payment_type', 'amount', 'date', 'echeance']);
    }

    #[Test]
    public function store_auto_allocates_when_order_id_provided(): void
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
            'invoice_no' => 'INV-MODAL-'.Str::random(4),
            'payment_type' => 'HandCash',
            'pay' => 0,
            'due' => 10000,
        ]);

        $response = $this->postJson("/payments/{$customer->id}", [
            'customer_id' => $customer->id,
            'nature' => 'CHQ-AUTO-001',
            'payment_type' => 'Cheque',
            'bank' => 'CIH',
            'date' => now()->format('d/m/Y'),
            'echeance' => now()->addMonth()->format('d/m/Y'),
            'amount' => 5000,
            'order_id' => $order->id,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('allocation.allocated_amount', 5000);
        $response->assertJsonPath('allocation.order.due', 5000);

        $this->assertDatabaseHas('order_payment', [
            'order_id' => $order->id,
            'allocated_amount' => 5000,
        ]);
    }

    #[Test]
    public function store_ignores_invalid_order_id(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $customer = $this->createCustomer($user);

        $response = $this->postJson("/payments/{$customer->id}", [
            'customer_id' => $customer->id,
            'nature' => 'CHQ-IGNORE-001',
            'payment_type' => 'Cheque',
            'bank' => 'CIH',
            'date' => now()->format('d/m/Y'),
            'echeance' => now()->addMonth()->format('d/m/Y'),
            'amount' => 5000,
            'order_id' => 99999,
        ]);

        $response->assertCreated();
        $response->assertJsonMissing(['allocation']);
    }
}
