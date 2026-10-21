<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:150'],
            'publisher' => ['nullable', 'string', 'max:150'],
            'publication_year' => ['nullable', 'integer', 'min:1800', 'max:'.(int) date('Y')],
            'isbn' => ['nullable', 'string', 'max:20', Rule::unique('books', 'isbn')],
            'stock_total' => ['required', 'integer', 'min:0', 'max:10000'],
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
            'publication_year.min' => 'Tahun terbit tidak masuk akal.',
            'publication_year.max' => 'Tahun terbit tidak boleh melebihi tahun ini.',
            'isbn.unique' => 'ISBN sudah terdaftar untuk buku lain.',
            'cover.image' => 'File harus berupa gambar.',
            'cover.max' => 'Ukuran cover maksimal 2MB.',
        ];
    }
}
