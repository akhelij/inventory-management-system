<?php

namespace App\Http\Controllers;

use App\Enums\MoroccanBank;
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
        $category = request('category');
        $permission = $category === 'b2c' ? PermissionEnum::READ_CLIENTS : PermissionEnum::READ_CUSTOMERS;
        abort_unless(auth()->user()->can($permission), 403);

        return view('customers.index', [
            'customers' => Customer::count(),
            'category' => $category,
        ]);
    }

    public function create(): View
    {
        $category = request('category', 'b2b');
        $permission = $category === 'b2c' ? PermissionEnum::CREATE_CLIENTS : PermissionEnum::CREATE_CUSTOMERS;
        abort_unless(auth()->user()->can($permission), 403);

        return view('customers.create', [
            'category' => $category,
            'banks' => MoroccanBank::cases(),
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $category = $request->input('category', 'b2b');
        $permission = $category === 'b2c' ? PermissionEnum::CREATE_CLIENTS : PermissionEnum::CREATE_CUSTOMERS;
        abort_unless(auth()->user()->can($permission), 403);

        $image = $request->hasFile('photo')
            ? $request->file('photo')->store('customers', 'public')
            : '';

        $data = array_merge($request->safe()->all(), [
            'user_id' => auth()->id(),
            'uuid' => Str::uuid(),
            'photo' => $image,
        ]);

        if ($category === 'b2c') {
            $data['limit'] = 999999999;
        }

        Customer::create($data);

        return to_route('customers.index', ['category' => $category])->with('success', 'New customer has been created!');
    }

    public function show(string $uuid): View
    {
        $allocationEnabled = Schema::hasTable('order_payment');

        $eagerLoads = $allocationEnabled
            ? ['orders.payments', 'payments.orders']
            : ['orders', 'payments'];

        $customer = Customer::where('uuid', $uuid)->with([...$eagerLoads, 'user', 'paymentSchedules.entries', 'paymentSchedules.order'])->firstOrFail();

        $permission = $customer->category?->value === 'b2c' ? PermissionEnum::READ_CLIENTS : PermissionEnum::READ_CUSTOMERS;
        abort_unless(auth()->user()->can($permission), 403);

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
            'banks' => MoroccanBank::cases(),
        ]);
    }

    public function edit(string $uuid): View
    {
        $customer = Customer::where('uuid', $uuid)->firstOrFail();

        $permission = $customer->category?->value === 'b2c' ? PermissionEnum::UPDATE_CLIENTS : PermissionEnum::UPDATE_CUSTOMERS;
        abort_unless(auth()->user()->can($permission), 403);

        return view('customers.edit', [
            'customer' => $customer,
            'banks' => MoroccanBank::cases(),
        ]);
    }

    public function update(UpdateCustomerRequest $request, string $uuid): RedirectResponse
    {
        $customer = Customer::where('uuid', $uuid)->firstOrFail();

        $category = $customer->category?->value ?? 'b2b';
        $permission = $category === 'b2c' ? PermissionEnum::UPDATE_CLIENTS : PermissionEnum::UPDATE_CUSTOMERS;
        abort_unless(auth()->user()->can($permission), 403);

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

        return to_route('customers.index', ['category' => $category])->with('success', 'Customer has been updated!');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $customer = Customer::where('uuid', $uuid)->firstOrFail();

        $category = $customer->category?->value ?? 'b2b';
        $permission = $category === 'b2c' ? PermissionEnum::DELETE_CLIENTS : PermissionEnum::DELETE_CUSTOMERS;
        abort_unless(auth()->user()->can($permission), 403);

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
