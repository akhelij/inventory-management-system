<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PermissionEnum;
use App\Enums\TaxType;
use App\Livewire\OrderUpdate;
use App\Livewire\Tables\OrderTable;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OrderApprovalStockTest extends TestCase
{
    private User $user;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();

        foreach ([PermissionEnum::UPDATE_ORDERS_STATUS, PermissionEnum::CREATE_ORDERS, PermissionEnum::UPDATE_ORDERS] as $permission) {
            Permission::findOrCreate($permission);
        }

        $this->user->givePermissionTo([
            PermissionEnum::UPDATE_ORDERS_STATUS,
            PermissionEnum::CREATE_ORDERS,
            PermissionEnum::UPDATE_ORDERS,
        ]);

        $this->actingAs($this->user);
        $this->customer = $this->createCustomer($this->user);
    }

    private function makeProduct(int $quantity): Product
    {
        return Product::factory()->create([
            'quantity' => $quantity,
            'tax_type' => TaxType::EXCLUSIVE,
            'uuid' => Str::uuid(),
            'slug' => fake()->unique()->slug(2),
            'code' => fake()->unique()->numerify('P####'),
            'user_id' => $this->user->id,
            'category_id' => Category::factory()->create(['user_id' => $this->user->id])->id,
            'unit_id' => Unit::factory()->create(['user_id' => $this->user->id])->id,
        ]);
    }

    private function makePendingOrder(array $lines): Order
    {
        $total = collect($lines)->sum(fn (array $line) => $line['qty'] * 100);

        $order = Order::create([
            'uuid' => Str::uuid(),
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'order_date' => now(),
            'order_status' => OrderStatus::PENDING,
            'total_products' => count($lines),
            'sub_total' => $total,
            'vat' => 0,
            'total' => $total,
            'invoice_no' => 'INV-'.fake()->unique()->numerify('######'),
            'payment_type' => 'HandCash',
            'pay' => 0,
            'due' => $total,
            'stock_affected' => false,
        ]);

        foreach ($lines as $line) {
            OrderDetails::create([
                'order_id' => $order->id,
                'product_id' => $line['product']->id,
                'quantity' => $line['qty'],
                'unitcost' => 100,
                'total' => $line['qty'] * 100,
            ]);
        }

        return $order;
    }

    #[Test]
    public function approval_via_order_table_is_blocked_when_stock_is_insufficient(): void
    {
        $product = $this->makeProduct(0);
        $order = $this->makePendingOrder([['product' => $product, 'qty' => 1]]);

        Livewire::test(OrderTable::class)
            ->call('initiateStatusUpdate', $order->id, OrderStatus::APPROVED)
            ->assertDispatched('orderStatusError');

        $order->refresh();
        $this->assertSame(OrderStatus::PENDING, $order->order_status);
        $this->assertFalse($order->stock_affected);
        $this->assertSame(0, $product->fresh()->quantity);
        $this->assertDatabaseMissing('stock_movements', ['order_id' => $order->id]);
    }

    #[Test]
    public function approval_via_order_table_deducts_stock_and_logs_movement(): void
    {
        $product = $this->makeProduct(5);
        $order = $this->makePendingOrder([['product' => $product, 'qty' => 2]]);

        Livewire::test(OrderTable::class)
            ->call('initiateStatusUpdate', $order->id, OrderStatus::APPROVED)
            ->assertDispatched('orderStatusUpdated');

        $order->refresh();
        $this->assertEquals(OrderStatus::APPROVED, $order->order_status);
        $this->assertTrue($order->stock_affected);
        $this->assertSame(3, $product->fresh()->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'movement_type' => 'deducted',
            'quantity' => 2,
            'balance_after' => 3,
        ]);
    }

    #[Test]
    public function second_order_cannot_be_approved_when_stock_is_exhausted(): void
    {
        $product = $this->makeProduct(1);
        $first = $this->makePendingOrder([['product' => $product, 'qty' => 1]]);
        $second = $this->makePendingOrder([['product' => $product, 'qty' => 1]]);

        Livewire::test(OrderTable::class)
            ->call('initiateStatusUpdate', $first->id, OrderStatus::APPROVED)
            ->assertDispatched('orderStatusUpdated');

        Livewire::test(OrderTable::class)
            ->call('initiateStatusUpdate', $second->id, OrderStatus::APPROVED)
            ->assertDispatched('orderStatusError');

        $this->assertEquals(OrderStatus::APPROVED, $first->fresh()->order_status);
        $this->assertSame(OrderStatus::PENDING, $second->fresh()->order_status);
        $this->assertSame(0, $product->fresh()->quantity);
    }

    #[Test]
    public function approval_refuses_non_positive_line_quantities(): void
    {
        $product = $this->makeProduct(0);
        $order = $this->makePendingOrder([['product' => $product, 'qty' => 0]]);

        Livewire::test(OrderTable::class)
            ->call('initiateStatusUpdate', $order->id, OrderStatus::APPROVED)
            ->assertDispatched('orderStatusError');

        $order->refresh();
        $this->assertSame(OrderStatus::PENDING, $order->order_status);
        $this->assertFalse($order->stock_affected);
        $this->assertSame(0, $product->fresh()->quantity);
        $this->assertDatabaseMissing('stock_movements', ['order_id' => $order->id]);
    }

    #[Test]
    public function canceled_orders_are_not_updatable(): void
    {
        $product = $this->makeProduct(5);
        $pending = $this->makePendingOrder([['product' => $product, 'qty' => 1]]);
        $canceled = $this->makePendingOrder([['product' => $product, 'qty' => 1]]);
        $canceled->update(['order_status' => OrderStatus::CANCELED]);

        $this->assertTrue($pending->fresh()->is_updatable_status);
        $this->assertFalse($canceled->fresh()->is_updatable_status);
    }

    #[Test]
    public function store_rejects_cart_items_with_non_positive_quantity(): void
    {
        $product = $this->makeProduct(5);

        $response = $this->post(route('orders.store'), [
            'customer_id' => $this->customer->id,
            'payment_type' => 'HandCash',
            'cart_data' => json_encode([
                ['product_id' => $product->id, 'qty' => 0, 'price' => 100, 'subtotal' => 0],
            ]),
        ]);

        $response->assertRedirect();
        $this->assertSame(0, Order::count());
        $this->assertSame(0, OrderDetails::count());
    }

    #[Test]
    public function store_rejects_cart_items_with_unknown_product(): void
    {
        $response = $this->post(route('orders.store'), [
            'customer_id' => $this->customer->id,
            'payment_type' => 'HandCash',
            'cart_data' => json_encode([
                ['product_id' => 999999, 'qty' => 1, 'price' => 100, 'subtotal' => 100],
            ]),
        ]);

        $response->assertRedirect();
        $this->assertSame(0, Order::count());
        $this->assertSame(0, OrderDetails::count());
    }

    #[Test]
    public function update_items_rejects_non_positive_quantity(): void
    {
        $product = $this->makeProduct(5);
        $order = $this->makePendingOrder([['product' => $product, 'qty' => 2]]);

        $this->post(route('orders.update_items', $order), [
            'product_id' => [$product->id],
            'quantity' => [0],
            'unitcost' => [100],
            'total' => [0],
        ]);

        $this->assertSame(2, OrderDetails::where('order_id', $order->id)
            ->where('product_id', $product->id)
            ->first()
            ->quantity);
    }

    #[Test]
    public function order_update_component_rejects_non_positive_quantity(): void
    {
        $product = $this->makeProduct(5);
        $order = $this->makePendingOrder([['product' => $product, 'qty' => 2]]);

        Livewire::test(OrderUpdate::class, ['order_id' => $order->id])
            ->call('updateQuantity', $product->id, 0);

        $this->assertSame(2, OrderDetails::where('order_id', $order->id)
            ->where('product_id', $product->id)
            ->first()
            ->quantity);
    }
}
