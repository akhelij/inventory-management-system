<?php

namespace Tests\Feature;

use App\Enums\PermissionEnum;
use App\Enums\TaxType;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OrderStoreLineTotalsTest extends TestCase
{
    private User $user;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
        Permission::findOrCreate(PermissionEnum::CREATE_ORDERS);
        $this->user->givePermissionTo(PermissionEnum::CREATE_ORDERS);
        $this->actingAs($this->user);
        $this->customer = $this->createCustomer($this->user);
    }

    #[Test]
    public function line_totals_come_from_quantity_and_price_not_the_submitted_subtotal(): void
    {
        $repriced = $this->makeProduct();
        $requantified = $this->makeProduct();

        $this->storeOrder([
            // Price raised to 2900 and submitted before the cart returned the new subtotal (INV-002904).
            ['product_id' => $repriced->id, 'qty' => 1, 'price' => 2900, 'subtotal' => 2650],
            // Quantity raised to 3 while the subtotal still covered 2 units (INV-002015).
            ['product_id' => $requantified->id, 'qty' => 3, 'price' => 4500, 'subtotal' => 9000],
        ]);

        $order = Order::with('details')->sole();

        $this->assertEquals(2900, $order->details->firstWhere('product_id', $repriced->id)->total);
        $this->assertEquals(13500, $order->details->firstWhere('product_id', $requantified->id)->total);
        $this->assertEquals(16400, $order->total);
        $this->assertEquals(16400, $order->sub_total);
        $this->assertEquals(16400, $order->due);
    }

    #[Test]
    public function gift_lines_stay_free_even_with_a_typed_price(): void
    {
        $product = $this->makeProduct();

        $this->storeOrder([
            ['product_id' => $product->id, 'qty' => 1, 'price' => 2650, 'subtotal' => 0, 'is_free' => true],
        ]);

        $line = Order::with('details')->sole()->details->sole();

        $this->assertEquals(0, $line->unitcost);
        $this->assertEquals(0, $line->total);
    }

    #[Test]
    public function line_total_matches_the_whole_number_unit_price_that_is_stored(): void
    {
        $product = $this->makeProduct();

        $this->storeOrder([
            ['product_id' => $product->id, 'qty' => 2, 'price' => 1050.5, 'subtotal' => 2101],
        ]);

        $line = Order::with('details')->sole()->details->sole();

        $this->assertEquals($line->quantity * $line->unitcost, $line->total);
    }

    private function storeOrder(array $cart): void
    {
        $this->post(route('orders.store'), [
            'customer_id' => $this->customer->id,
            'payment_type' => 'HandCash',
            'cart_data' => json_encode($cart),
        ])->assertRedirect(route('orders.index'));
    }

    private function makeProduct(): Product
    {
        return Product::factory()->create([
            'quantity' => 10,
            'tax_type' => TaxType::EXCLUSIVE,
            'uuid' => Str::uuid(),
            'slug' => fake()->unique()->slug(2),
            'code' => fake()->unique()->numerify('P####'),
            'user_id' => $this->user->id,
            'category_id' => Category::factory()->create(['user_id' => $this->user->id])->id,
            'unit_id' => Unit::factory()->create(['user_id' => $this->user->id])->id,
        ]);
    }
}
