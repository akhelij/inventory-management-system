<?php

namespace App\Livewire\Tables;

use App\Models\OrderDetails;
use Livewire\Component;
use App\Models\Product;
use Livewire\WithPagination;
use Gloudemans\Shoppingcart\Facades\Cart;

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

    public function mount($order_id=null)
    {
        $this->order_id = $order_id;
    }

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;

        } else {
            $this->sortAsc = true;
        }

        $this->sortField = $field;
    }

    public function addCartItem($id, $name, $price)
    {
        if($this->order_id) {
            $this->updateOrderDetails($id, $price);
        }

        Cart::add([
            'id' => $id,
            'name' => $name,
            'qty' => 1,
            'price' => $price,
            'weight' => 1,
        ]);

        // Emit an event to notify other components that the cart has been updated
        $this->dispatch('item-added');
    }

    public function updateOrderDetails($product_id, $unitcost)
    {
        if(!$this->order_id){
            $this->addCartItem();
        }

        $order_details = OrderDetails::where('order_id', $this->order_id)
            ->where('product_id', $product_id)
            ->first();

        if($order_details) {
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
            'products' => Product::with(['category', 'unit'])
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->paginate($this->perPage)
        ]);
    }
}
