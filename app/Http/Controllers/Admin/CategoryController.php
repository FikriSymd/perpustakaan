<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->withCount('books')
            ->search($request->string('q')->toString())
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'search' => $request->string('q')->toString(),
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Kategori \"{$category->name}\" berhasil ditambahkan.");
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Kategori \"{$category->name}\" berhasil diperbarui.");
    }

    public function destroy(Category $category)
    {
        if ($category->books()->exists()) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', "Kategori \"{$category->name}\" masih memiliki buku. Pindahkan atau hapus bukunya dulu.");
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Kategori \"{$category->name}\" berhasil dihapus.");
    }
}
