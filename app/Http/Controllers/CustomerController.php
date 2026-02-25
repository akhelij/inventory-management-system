<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Exports\PendingPaymentsExport;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_CUSTOMERS), 403);

        return view('customers.index', [
            'customers' => Customer::count(),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_CUSTOMERS), 403);

        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_CUSTOMERS), 403);

        $image = $request->hasFile('photo')
            ? $request->file('photo')->store('customers', 'public')
            : '';

        Customer::create(array_merge($request->safe()->all(), [
            'user_id' => auth()->id(),
            'uuid' => Str::uuid(),
            'photo' => $image,
        ]));

        return to_route('customers.index')->with('success', 'New customer has been created!');
    }

    public function show(string $uuid): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_CUSTOMERS), 403);

        $allocationEnabled = Schema::hasTable('order_payment');

        $eagerLoads = $allocationEnabled
            ? ['orders.payments', 'payments.orders']
            : ['orders', 'payments'];

        $customer = Customer::where('uuid', $uuid)->with($eagerLoads)->firstOrFail();

        $due = $customer->total_orders - $customer->total_payments;

        return view('customers.show', [
            'customer' => $customer,
            'totalOrders' => $customer->total_orders,
            'totalPayments' => $customer->total_payments,
            'due' => $due,
            'amountPendingPayments' => $customer->total_pending_payments,
            'diff' => $due,
            'limit_reached' => $customer->is_out_of_limit,
            'allocationEnabled' => $allocationEnabled,
        ]);
    }

    public function edit(string $uuid): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_CUSTOMERS), 403);

        $customer = Customer::where('uuid', $uuid)->firstOrFail();

        return view('customers.edit', [
            'customer' => $customer,
        ]);
    }

    public function update(UpdateCustomerRequest $request, string $uuid): RedirectResponse
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_CUSTOMERS), 403);

        $customer = Customer::where('uuid', $uuid)->firstOrFail();

        $image = $customer->photo;
        if ($request->hasFile('photo')) {
            if ($customer->photo) {
                unlink(public_path('storage/').$customer->photo);
            }
            $image = $request->file('photo')->store('customers', 'public');
        }

        $customer->update(array_merge($request->safe()->all(), [
            'photo' => $image,
        ]));

        return to_route('customers.index')->with('success', 'Customer has been updated!');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        abort_unless(auth()->user()->can(PermissionEnum::DELETE_CUSTOMERS), 403);

        $customer = Customer::where('uuid', $uuid)->firstOrFail();

        if ($customer->photo) {
            unlink(public_path('storage/customers/').$customer->photo);
        }

        $customer->delete();

        return redirect()->back()->with('success', 'Customer has been deleted!');
    }

    public function downloadPayments(Customer $customer): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_CUSTOMERS), 403);

        return view('customer.print-payments', [
            'payments' => $customer->payments,
        ]);
    }

    public function exportPendingPayments(string $uuid)
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_CUSTOMERS), 403);

        $customer = Customer::where('uuid', $uuid)->with('payments')->firstOrFail();

        return (new PendingPaymentsExport($customer))->export();
    }
}
