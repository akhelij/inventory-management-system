<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use Livewire\Attributes\On;
use Livewire\Component;

class OrderUpdate extends Component
{
    public int $order_id;

    public function mount(int $order_id): void
    {
        $this->order_id = $order_id;
    }

    public function delete(int $id): void
    {
        OrderDetails::where('id', $id)->delete();
        $this->updateOrder();
    }

    public function updateQuantity(int $product_id, int $quantity): void
    {
        $product = Product::find($product_id);

        if ($product && $quantity > $product->quantity) {
            session()->flash('warning', "Warning: Requested quantity ({$quantity}) exceeds available stock ({$product->quantity}) for {$product->name}. Order may not be approvable.");
        }

        $order_details = OrderDetails::where('order_id', $this->order_id)
            ->where('product_id', $product_id)
            ->first();

        $order_details->update([
            'quantity' => $quantity,
            'total' => $quantity * $order_details->unitcost,
        ]);

        $this->updateOrder();
    }

    public function updateOrder(): Order
    {
        $order = Order::find($this->order_id);
        $details = OrderDetails::where('order_id', $this->order_id)->get();
        $total = $details->sum('total');
        $due = $total - ($order->pay ?? 0);

        $order->update([
            'total_products' => $details->count(),
            'sub_total' => $total,
            'vat' => 0,
            'total' => $total,
            'due' => $due,
        ]);

        return $order;
    }

    #[On('item-added')]
    public function render()
    {
        $order_details = OrderDetails::where('order_id', $this->order_id)->get();
        $order = $this->updateOrder();

        return view('livewire.order-update', compact('order_details', 'order'));
    }
}
