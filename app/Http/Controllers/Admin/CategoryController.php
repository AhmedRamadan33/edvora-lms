<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request, CategoryService $categories): View
    {
        $categories = $categories->paginate(search: $request->string('search')->trim()->toString() ?: null);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request, CategoryService $categories): RedirectResponse
    {
        $categories->create($request->validated(), $request->boolean('is_active', true));

        return redirect()->route('admin.categories.index')->with('success', __('Category created.'));
    }

    public function edit(Category $category): View
    {
        $category->load('translations');

        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category, CategoryService $categories): RedirectResponse
    {
        $categories->update($category, $request->validated(), $request->boolean('is_active', true));

        return redirect()->route('admin.categories.index')->with('success', __('Category updated.'));
    }

    public function destroy(Category $category, CategoryService $categories): RedirectResponse
    {
        $categories->delete($category);

        return back()->with('success', __('Category deleted.'));
    }
}
