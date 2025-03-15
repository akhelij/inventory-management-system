<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use Str;

class CategoryController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_CATEGORIES), 403);
        $categories = Category::count();

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_CATEGORIES), 403);

        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_CATEGORIES), 403);
        Category::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category has been created!');
    }

    public function show(Category $category)
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_CATEGORIES), 403);

        return view('categories.show', [
            'category' => $category,
        ]);
    }

    public function edit(Category $category)
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_CATEGORIES), 403);

        return view('categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_CATEGORIES), 403);
        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category has been updated!');
    }

    public function destroy(Category $category)
    {
        abort_unless(auth()->user()->can(PermissionEnum::DELETE_CATEGORIES), 403);
        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category has been deleted!');
    }
}
