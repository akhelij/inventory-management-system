<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Models\Customer;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Payment;
use Illuminate\Http\Request;
use Str;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_CUSTOMERS), 403);

        return view('customers.index', [
            'customers' => Customer::count()
        ]);
    }

    public function create()
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_CUSTOMERS), 403);
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_CUSTOMERS), 403);

        $image = "";
        if ($request->hasFile('photo')) {
            $image = $request->file('photo')->store("customers", "public");
        }

        $data = $request->safe()->all();

        $data = array_merge($data, [
            "user_id" => auth()->id(),
            "uuid" => Str::uuid(),
            'photo' => $image,
        ]);

        Customer::create($data);

        return redirect()
            ->route('customers.index')
            ->with('success', 'New customer has been created!');
    }

    public function show($uuid)
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_CUSTOMERS), 403);
        $customer = Customer::where("uuid", $uuid)->with(['orders', 'payments'])->firstOrFail();
        $totalOrders = $customer->total_orders;
        $totalOrdersPaid = $customer->total_orders_paid;
        $due = $totalOrders - $customer->total_payments;
        $diff = $customer->total_orders_not_paid;
        $amountPendingPayments = $customer->total_pending_payments;
        $limit_reached = $diff > $customer->limit;
        return view('customers.show', [
            'customer' => $customer,
            'totalOrders' => $totalOrders,
            'totalOrdersPaid' => $totalOrdersPaid,
            'due' => $due,
            'amountPendingPayments' => $amountPendingPayments,
            'diff' => $diff,
            'limit_reached' => $limit_reached
        ]);
    }

    public function edit($uuid)
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_CUSTOMERS), 403);
        $customer = Customer::where("uuid", $uuid)->firstOrFail();
        return view('customers.edit', [
            'customer' => $customer
        ]);
    }

    public function update(UpdateCustomerRequest $request, $uuid)
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_CUSTOMERS), 403);
        $customer = Customer::where("uuid", $uuid)->firstOrFail();

        /**
         * Handle upload image with Storage.
         */
        $image = $customer->photo;
        if ($request->hasFile('photo')) {
            if ($customer->photo) {
                unlink(public_path('storage/') . $customer->photo);
            }
            $image = $request->file('photo')->store("customers", "public");
        }

        $data = $request->safe()->all();

        $data = array_merge($data, [
            'photo' => $image,
        ]);

        $customer->update($data);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer has been updated!');
    }

    public function destroy($uuid)
    {
        abort_unless(auth()->user()->can(PermissionEnum::DELETE_CUSTOMERS), 403);
        $customer = Customer::where("uuid", $uuid)->firstOrFail();
        if ($customer->photo) {
            unlink(public_path('storage/customers/') . $customer->photo);
        }

        $customer->delete();

        return redirect()
            ->back()
            ->with('success', 'Customer has been deleted!');
    }

    public function downloadPayments(Customer $customer)
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_CUSTOMERS), 403);
        return view('customer.print-payments', [
            'payments' => $customer->payments,
        ]);
    }
}
