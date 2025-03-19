<?php

namespace App\Livewire\Tables;

use App\Models\OrderDetails;
use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public $perPage = 15;

    public $search = '';

    public $sortField = 'id';

    public $sortAsc = false;

    public $product;

    public $id;

    public $name;

    public $price;

    public $order_id;

    public function mount($order_id = null)
    {
        $this->order_id = $order_id;
    }

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortAsc = ! $this->sortAsc;

        } else {
            $this->sortAsc = true;
        }

        $this->sortField = $field;
    }

    public function addCartItem($id, $name, $price)
    {
        if ($this->order_id) {
            $this->updateOrderDetails($id, $price);
        }

        Cart::add([
            'id' => $id,
            'name' => $name,
            'qty' => 1,
            'price' => $price,
            'weight' => 1,
        ]);

        // Store the cart in the database if user is authenticated
        $this->storeCart();

        // Emit an event to notify other components that the cart has been updated
        $this->dispatch('item-added');
    }

    /**
     * Store the current cart in the database
     */
    private function storeCart(): void
    {
        if (auth()->check()) {
            try {
                // Delete existing cart before storing the new one
                Cart::erase(auth()->id());
                Cart::store(auth()->id());
            } catch (\Exception $e) {
                // Log error or handle silently
            }
        }
    }

    public function updateOrderDetails($product_id, $unitcost)
    {
        if (! $this->order_id) {
            $this->addCartItem();
        }

        $order_details = OrderDetails::where('order_id', $this->order_id)
            ->where('product_id', $product_id)
            ->first();

        if ($order_details) {
            $order_details->update([
                'quantity' => $order_details->quantity + 1,
                'total' => $order_details->total + $unitcost,
            ]);
        } else {
            OrderDetails::create([
                'order_id' => $this->order_id,
                'product_id' => $product_id,
                'quantity' => 1,
                'unitcost' => $unitcost,
                'total' => $unitcost,
            ]);
        }

        // Emit an event to notify other components that the cart has been updated
        $this->dispatch('item-added');
    }

    public function render()
    {
        return view('livewire.tables.product-list')->with([
            'products' => Product::query()
                ->with(['category', 'warehouse', 'unit'])
                ->where('quantity', '>', 0)
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->paginate($this->perPage),
        ]);
    }
}
