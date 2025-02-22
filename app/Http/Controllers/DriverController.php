<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        return view('drivers.index', [
            'drivers' => Driver::paginate(20)
        ]);
    }

    public function create()
    {
        return view('drivers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255'
        ]);

        Driver::create($request->all());

        return redirect()
            ->route('drivers.index')
            ->with('success', 'Driver has been created!');
    }

    public function edit(Driver $driver)
    {
        return view('drivers.edit', [
            'driver' => $driver
        ]);
    }

    public function update(Request $request, Driver $driver)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255'
        ]);

        $driver->update($request->all());

        return redirect()
            ->route('drivers.index')
            ->with('success', 'Driver has been updated!');
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();
        return redirect()
            ->route('drivers.index')
            ->with('success', 'Driver has been deleted!');
    }
}
