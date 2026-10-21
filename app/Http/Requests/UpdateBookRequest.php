<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $book = $this->route('book');

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:150'],
            'publisher' => ['nullable', 'string', 'max:150'],
            'publication_year' => ['nullable', 'integer', 'min:1800', 'max:'.(int) date('Y')],
            'isbn' => ['nullable', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($book?->id)],
            // Stok total tidak boleh lebih kecil dari jumlah yang sedang dipinjam.
            'stock_total' => [
                'required', 'integer', 'min:0', 'max:10000',
                function (string $attribute, mixed $value, \Closure $fail) use ($book) {
                    // Eksemplar yang sedang keluar = stok total - stok tersedia
                    $onLoan = (int) ($book?->stock_total ?? 0) - (int) ($book?->stock_available ?? 0);
                    if ((int) $value < $onLoan) {
                        $fail("Stok total tidak boleh lebih kecil dari {$onLoan} (jumlah eksemplar yang sedang dipinjam).");
                    }
                },
            ],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak ditemukan.',
            'title.required' => 'Judul buku wajib diisi.',
            'author.required' => 'Penulis wajib diisi.',
            'isbn.unique' => 'ISBN sudah terdaftar untuk buku lain.',
            'cover.image' => 'File harus berupa gambar.',
            'cover.max' => 'Ukuran cover maksimal 2MB.',
        ];
    }
}
