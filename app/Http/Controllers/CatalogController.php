<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::withCount('books')->orderBy('name')->get();

        $activeCategory = null;
        if ($slug = $request->string('kategori')->toString()) {
            $activeCategory = Category::where('slug', $slug)->first();
        }

        $books = Book::query()
            ->with('category')
            ->search($request->string('q')->toString())
            ->inCategory($activeCategory)
            ->when($request->boolean('tersedia'), fn ($q) => $q->available())
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        return view('catalog.index', [
            'books' => $books,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'search' => $request->string('q')->toString(),
        ]);
    }

    public function show(Book $book): View
    {
        $book->load('category');

        $related = Book::query()
            ->with('category:id,name,slug')
            ->where('category_id', $book->category_id)
            ->whereKeyNot($book->id)
            ->orderBy('title')
            ->limit(4)
            ->get();

        return view('catalog.show', [
            'book' => $book,
            'related' => $related,
        ]);
    }
}
