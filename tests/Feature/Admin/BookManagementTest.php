<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_dapat_melihat_daftar_buku(): void
    {
        Book::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get(route('admin.books.index'))
            ->assertOk()
            ->assertSee(Book::first()->title);
    }

    public function test_anggota_tidak_bisa_akses_area_admin(): void
    {
        $member = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->get(route('admin.books.index'))
            ->assertForbidden();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('admin.books.index'))->assertRedirect('/login');
    }

    public function test_admin_dapat_menambahkan_buku(): void
    {
        $category = Category::create(['name' => 'Fiksi']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.books.store'), [
                'category_id' => $category->id,
                'title' => 'Buku Uji Coba',
                'author' => 'Penulis Uji',
                'stock_total' => 3,
            ]);

        $response->assertRedirect(route('admin.books.index'));

        $book = Book::where('title', 'Buku Uji Coba')->firstOrFail();
        $this->assertSame(3, $book->stock_available);
        $this->assertSame('buku-uji-coba', $book->slug);
    }

    public function test_validasi_isbn_duplikat_ditolak(): void
    {
        $category = Category::create(['name' => 'Fiksi']);
        Book::factory()->create(['category_id' => $category->id, 'isbn' => '9781234567890']);

        $this->actingAs($this->admin)
            ->post(route('admin.books.store'), [
                'category_id' => $category->id,
                'title' => 'Buku Lain',
                'author' => 'Penulis',
                'stock_total' => 1,
                'isbn' => '9781234567890',
            ])
            ->assertSessionHasErrors('isbn');
    }

    public function test_validasi_tahun_terbit_masa_depan_ditolak(): void
    {
        $category = Category::create(['name' => 'Fiksi']);

        $this->actingAs($this->admin)
            ->post(route('admin.books.store'), [
                'category_id' => $category->id,
                'title' => 'Buku dari Masa Depan',
                'author' => 'Penulis',
                'stock_total' => 1,
                'publication_year' => (int) date('Y') + 5,
            ])
            ->assertSessionHasErrors('publication_year');
    }

    public function test_upload_cover_tersimpan(): void
    {
        Storage::fake('public');
        $category = Category::create(['name' => 'Fiksi']);

        $this->actingAs($this->admin)
            ->post(route('admin.books.store'), [
                'category_id' => $category->id,
                'title' => 'Buku Bersampul',
                'author' => 'Penulis',
                'stock_total' => 1,
                'cover' => UploadedFile::fake()->image('cover.jpg', 300, 450),
            ])
            ->assertRedirect(route('admin.books.index'));

        $book = Book::where('title', 'Buku Bersampul')->firstOrFail();
        $this->assertNotNull($book->cover_path);
        Storage::disk('public')->assertExists($book->cover_path);
    }

    public function test_stok_tersedia_tidak_akan_negatif_saat_kurangi_stok_total(): void
    {
        $category = Category::create(['name' => 'Fiksi']);
        $book = Book::factory()->create(['category_id' => $category->id, 'stock_total' => 5, 'stock_available' => 3]);

        // 2 sedang dipinjam; coba set stok total menjadi 1 (di bawah jumlah dipinjam) -> harus ditolak
        $this->actingAs($this->admin)
            ->put(route('admin.books.update', $book), [
                'category_id' => $category->id,
                'title' => $book->title,
                'author' => $book->author,
                'stock_total' => 1,
            ])
            ->assertSessionHasErrors('stock_total');

        $book->refresh();
        $this->assertSame(5, $book->stock_total);
    }

    public function test_buku_yang_sedang_dipinjam_tidak_bisa_dihapus(): void
    {
        $category = Category::create(['name' => 'Fiksi']);
        $book = Book::factory()->create(['category_id' => $category->id, 'stock_total' => 2, 'stock_available' => 1]);

        $member = User::factory()->anggota()->create();
        \App\Models\Loan::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'borrowed_at' => today(),
            'due_at' => today()->addDays(14),
            'status' => \App\Models\Loan::STATUS_DIPINJAM,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.books.destroy', $book))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }
}
