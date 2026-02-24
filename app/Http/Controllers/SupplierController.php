<?php

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        return view('suppliers.index', [
            'suppliers' => Supplier::where('user_id', auth()->id())->count(),
        ]);
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $image = $request->hasFile('photo')
            ? $request->file('photo')->store('supliers', 'public')
            : '';

        Supplier::create([
            'user_id' => auth()->id(),
            'uuid' => Str::uuid(),
            'photo' => $image,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'shopname' => $request->shopname,
            'type' => $request->type,
            'account_holder' => $request->account_holder,
            'account_number' => $request->account_number,
            'bank_name' => $request->bank_name,
            'address' => $request->address,
        ]);

        return to_route('suppliers.index')->with('success', 'New supplier has been created!');
    }

    public function show(string $uuid): View
    {
        $supplier = Supplier::where('uuid', $uuid)->firstOrFail();
        $supplier->loadMissing('purchases');

        return view('suppliers.show', [
            'supplier' => $supplier,
        ]);
    }

    public function edit(string $uuid): View
    {
        return view('suppliers.edit', [
            'supplier' => Supplier::where('uuid', $uuid)->firstOrFail(),
        ]);
    }

    public function update(UpdateSupplierRequest $request, string $uuid): RedirectResponse
    {
        $supplier = Supplier::where('uuid', $uuid)->firstOrFail();

        $image = $supplier->photo;
        if ($request->hasFile('photo')) {
            if ($supplier->photo) {
                unlink(public_path('storage/').$supplier->photo);
            }
            $image = $request->file('photo')->store('supliers', 'public');
        }

        $supplier->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'photo' => $image,
            'shopname' => $request->shopname,
            'type' => $request->type,
            'account_holder' => $request->account_holder,
            'account_number' => $request->account_number,
            'bank_name' => $request->bank_name,
            'address' => $request->address,
        ]);

        return to_route('suppliers.index')->with('success', 'Supplier has been updated!');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $supplier = Supplier::where('uuid', $uuid)->firstOrFail();

        if ($supplier->photo) {
            unlink(public_path('storage/suppliers/').$supplier->photo);
        }

        $supplier->delete();

        return to_route('suppliers.index')->with('success', 'Supplier has been deleted!');
    }
}
