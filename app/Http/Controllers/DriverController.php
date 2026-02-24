<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function index(): View
    {
        return view('drivers.index', [
            'drivers' => Driver::paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('drivers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255',
        ]);

        Driver::create($request->all());

        return to_route('drivers.index')->with('success', 'Driver has been created!');
    }

    public function edit(Driver $driver): View
    {
        return view('drivers.edit', [
            'driver' => $driver,
        ]);
    }

    public function update(Request $request, Driver $driver): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255',
        ]);

        $driver->update($request->all());

        return to_route('drivers.index')->with('success', 'Driver has been updated!');
    }

    public function destroy(Driver $driver): RedirectResponse
    {
        $driver->delete();

        return to_route('drivers.index')->with('success', 'Driver has been deleted!');
    }
}
