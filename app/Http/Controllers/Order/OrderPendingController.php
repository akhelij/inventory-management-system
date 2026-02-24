<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderPendingController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('orders.pending-orders', [
            'orders' => Order::where('order_status', OrderStatus::PENDING)->latest()->get(),
        ]);
    }
}
