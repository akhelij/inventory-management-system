<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PermissionEnum;
use App\Enums\TaxType;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OrderProductCodeDisplayTest extends TestCase
{
    private User $user;

    private Product $product;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
        Permission::findOrCreate(PermissionEnum::READ_ORDERS);
        $this->user->givePermissionTo(PermissionEnum::READ_ORDERS);
        $this->actingAs($this->user);

        $this->product = Product::factory()->create([
            'quantity' => 10,
            'tax_type' => TaxType::EXCLUSIVE,
            'uuid' => Str::uuid(),
            'slug' => fake()->unique()->slug(2),
            'code' => 'REF-ZX9481',
            'user_id' => $this->user->id,
            'category_id' => Category::factory()->create(['user_id' => $this->user->id])->id,
            'unit_id' => Unit::factory()->create(['user_id' => $this->user->id])->id,
            'warehouse_id' => Warehouse::create(['name' => 'Main'])->id,
        ]);

        $this->order = Order::create([
            'uuid' => Str::uuid(),
            'user_id' => $this->user->id,
            'customer_id' => $this->createCustomer($this->user)->id,
            'order_date' => now(),
            'order_status' => OrderStatus::APPROVED,
            'total_products' => 1,
            'sub_total' => 200,
            'vat' => 0,
            'total' => 200,
            'invoice_no' => 'INV-'.fake()->unique()->numerify('######'),
            'payment_type' => 'HandCash',
            'pay' => 0,
            'due' => 200,
            'stock_affected' => false,
        ]);

        OrderDetails::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unitcost' => 100,
            'total' => 200,
        ]);
    }

    #[Test]
    public function order_show_page_displays_product_code(): void
    {
        $this->get(route('orders.show', $this->order->uuid))
            ->assertOk()
            ->assertSee('REF-ZX9481');
    }

    #[Test]
    public function printed_invoice_displays_product_code(): void
    {
        $order = $this->order->load(['customer', 'details.product', 'user', 'payments']);

        $this->view('orders.pdf-invoice', ['order' => $order])
            ->assertSee('REF-ZX9481');
    }
}
