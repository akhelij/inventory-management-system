<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_CATEGORIES), 403);

        return view('categories.index', [
            'categories' => Category::count(),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_CATEGORIES), 403);

        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_CATEGORIES), 403);

        Category::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return to_route('categories.index')->with('success', 'Category has been created!');
    }

    public function show(Category $category): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_CATEGORIES), 403);

        return view('categories.show', [
            'category' => $category,
        ]);
    }

    public function edit(Category $category): View
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_CATEGORIES), 403);

        return view('categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_CATEGORIES), 403);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return to_route('categories.index')->with('success', 'Category has been updated!');
    }

    public function destroy(Category $category): RedirectResponse
    {
        abort_unless(auth()->user()->can(PermissionEnum::DELETE_CATEGORIES), 403);

        $category->delete();

        return to_route('categories.index')->with('success', 'Category has been deleted!');
    }
}
