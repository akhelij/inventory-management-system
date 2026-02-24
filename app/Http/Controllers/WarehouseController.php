<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        return view('warehouses.index', [
            'warehouses' => Warehouse::paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('warehouses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Warehouse::create($request->all());

        return to_route('warehouses.index')->with('success', 'Warehouse has been created!');
    }

    public function show(Warehouse $warehouse): View
    {
        return view('warehouses.show', [
            'warehouse' => $warehouse,
        ]);
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('warehouses.edit', [
            'warehouse' => $warehouse,
        ]);
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $warehouse->update($request->all());

        return to_route('warehouses.index')->with('success', 'Warehouse has been updated!');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->delete();

        return to_route('warehouses.index')->with('success', 'Warehouse has been deleted!');
    }
}
