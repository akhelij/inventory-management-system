<?php

namespace App\Http\Controllers;

use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(): View
    {
        return view('units.index', [
            'units' => Unit::select(['id', 'name', 'slug', 'short_code'])->get(),
        ]);
    }

    public function create(): View
    {
        return view('units.create');
    }

    public function show(Unit $unit): View
    {
        $unit->loadMissing('products');

        return view('units.show', [
            'unit' => $unit,
        ]);
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        Unit::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'short_code' => $request->short_code,
        ]);

        return to_route('units.index')->with('success', 'Unit has been created!');
    }

    public function edit(Unit $unit): View
    {
        return view('units.edit', [
            'unit' => $unit,
        ]);
    }

    public function update(UpdateUnitRequest $request, string $slug): RedirectResponse
    {
        $unit = Unit::where(['user_id' => auth()->id(), 'slug' => $slug])->firstOrFail();

        $unit->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'short_code' => $request->short_code,
        ]);

        return to_route('units.index')->with('success', 'Unit has been updated!');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();

        return to_route('units.index')->with('success', 'Unit has been deleted!');
    }
}
