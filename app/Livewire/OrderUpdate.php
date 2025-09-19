<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderDetails;
use Livewire\Attributes\On;
use Livewire\Component;

class OrderUpdate extends Component
{
    public $order_id;

    public function mount($order_id)
    {
        $this->order_id = $order_id;
    }

    public function delete($id)
    {
        OrderDetails::where('id', $id)->delete();
        $this->updateOrder(); // Immediately recalculate totals after deletion
    }

    public function updateQuantity($product_id, $quantity)
    {
        $order_details = OrderDetails::where('order_id', $this->order_id)->where('product_id', $product_id)->first();
        $order_details->update([
            'quantity' => $quantity,
            'total' => $quantity * $order_details->unitcost,
        ]);
        $this->updateOrder(); // Immediately recalculate totals after quantity update
    }

    public function updateOrder()
    {
        $order = Order::find($this->order_id);
        $details = OrderDetails::where('order_id', $this->order_id)->get();
        $total = 0;

        foreach ($details as $item) {
            $total += $item->total;
        }

        // Calculate due amount considering existing payments
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

        return view('livewire.order-update')->with(compact('order_details', 'order'));
    }
}
