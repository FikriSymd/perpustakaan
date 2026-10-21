<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $books = Book::query()
            ->with('category')
            ->withCount(['activeLoans'])
            ->search($request->string('q')->toString())
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.books.index', [
            'books' => $books,
            'search' => $request->string('q')->toString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.books.create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(StoreBookRequest $request)
    {
        $data = $this->validatedBookData($request);
        $data['stock_available'] = $data['stock_total'];

        $book = Book::create($data);

        if ($request->hasFile('cover')) {
            $book->update(['cover_path' => $this->storeCover($request, $book)]);
        }

        return redirect()
            ->route('admin.books.index')
            ->with('success', "Buku \"{$book->title}\" berhasil ditambahkan.");
    }

    public function edit(Book $book): View
    {
        return view('admin.books.edit', [
            'book' => $book->load('category'),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $data = $this->validatedBookData($request);

        // Selisih stok total mengubah stok tersedia (eksemplar yang sedang keluar tetap terhitung).
        $onLoan = (int) $book->stock_total - (int) $book->stock_available;
        $data['stock_available'] = max(0, (int) $data['stock_total'] - $onLoan);

        if ($request->hasFile('cover')) {
            if ($book->cover_path) {
                Storage::disk('public')->delete($book->cover_path);
            }
            $data['cover_path'] = $this->storeCover($request, $book);
        }

        $book->update($data);

        return redirect()
            ->route('admin.books.index')
            ->with('success', "Buku \"{$book->title}\" berhasil diperbarui.");
    }

    public function destroy(Book $book)
    {
        $onLoan = (int) $book->activeLoans()->count();

        if ($onLoan > 0) {
            return redirect()
                ->route('admin.books.index')
                ->with('error', "Buku \"{$book->title}\" tidak dapat dihapus karena masih ada {$onLoan} eksemplar yang sedang dipinjam.");
        }

        if ($book->cover_path) {
            Storage::disk('public')->delete($book->cover_path);
        }

        $book->delete();

        return redirect()
            ->route('admin.books.index')
            ->with('success', "Buku \"{$book->title}\" berhasil dihapus.");
    }

    private function validatedBookData(StoreBookRequest|UpdateBookRequest $request): array
    {
        $validated = $request->safe()->except(['cover']);

        return collect($validated)->map(fn ($v) => $v === '' ? null : $v)->all();
    }

    private function storeCover(StoreBookRequest|UpdateBookRequest $request, Book $book): string
    {
        $file = $request->file('cover');

        return $file->store("covers/{$book->id}", 'public');
    }
}
