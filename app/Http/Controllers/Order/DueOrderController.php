<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DueOrderController extends Controller
{
    public function index(): View
    {
        return view('due.index', [
            'orders' => Order::where('due', '>', 0)->latest()->get(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->loadMissing(['customer', 'details']);

        return view('due.show', [
            'order' => $order,
        ]);
    }

    public function edit(Order $order): View
    {
        $order->loadMissing(['customer', 'details']);

        return view('due.edit', [
            'order' => $order,
            'customers' => Customer::select(['id', 'name'])->get(),
        ]);
    }

    public function update(Order $order, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pay' => 'required|numeric',
        ]);

        $order->update([
            'due' => $order->due - $validated['pay'],
            'pay' => $order->pay + $validated['pay'],
        ]);

        return to_route('due.index')->with('success', 'Due amount has been updated!');
    }
}
