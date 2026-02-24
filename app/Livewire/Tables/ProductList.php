<?php

namespace App\Livewire\Tables;

use App\Models\OrderDetails;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public int $perPage = 15;

    public string $search = '';

    public string $sortField = 'id';

    public bool $sortAsc = false;

    public ?int $order_id = null;

    public function mount(?int $order_id = null): void
    {
        $this->order_id = $order_id;
    }

    public function sortBy(string $field): void
    {
        $this->sortAsc = $this->sortField === $field ? ! $this->sortAsc : true;
        $this->sortField = $field;
    }

    public function addCartItem(int $id, string $name, float $price): void
    {
        if ($this->order_id) {
            $this->updateOrderDetails($id, $price);
        }

        app(CartService::class)->addItem(auth()->id(), $id, $name, $price, 1);

        $this->dispatch('item-added');
    }

    public function updateOrderDetails(int $product_id, float $unitcost): void
    {
        if (! $this->order_id) {
            return;
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

        $this->dispatch('item-added');
    }

    public function render()
    {
        return view('livewire.tables.product-list', [
            'products' => Product::query()
                ->with(['category', 'warehouse', 'unit'])
                ->where('quantity', '>', 0)
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->paginate($this->perPage),
        ]);
    }
}
