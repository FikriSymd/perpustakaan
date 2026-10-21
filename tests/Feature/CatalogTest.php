<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function seedDatabase(): void
    {
        $this->seed(DatabaseSeeder::class);
    }

    public function test_katalog_bisa_diakses_publik(): void
    {
        $this->seedDatabase();

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Koleksi Perpustakaan')
            ->assertSee('Laskar Pelangi');
    }

    public function test_pencarian_berdasarkan_judul(): void
    {
        $this->seedDatabase();

        $this->get(route('catalog.index', ['q' => 'Pramoedya']))
            ->assertOk()
            ->assertSee('Bumi Manusia')
            ->assertDontSee('Laskar Pelangi');
    }

    public function test_filter_kategori(): void
    {
        $this->seedDatabase();

        $category = Category::where('slug', 'anak-remaja')->firstOrFail();

        $this->get(route('catalog.index', ['kategori' => $category->slug]))
            ->assertOk()
            ->assertSee('Percy Jackson')
            ->assertDontSee('Atomic Habits');
    }

    public function test_filter_hanya_tersedia(): void
    {
        $this->seedDatabase();

        // Pinjamkan semua stok satu buku supaya habis
        $book = Book::where('stock_total', 1)->firstOr(function () {
            $book = Book::factory()->create(['stock_total' => 1, 'stock_available' => 1]);

            return $book;
        });

        Loan::create([
            'user_id' => User::where('role', User::ROLE_ANGGOTA)->firstOrFail()->id,
            'book_id' => $book->id,
            'borrowed_at' => today(),
            'due_at' => today()->addDays(14),
            'status' => Loan::STATUS_DIPINJAM,
        ]);
        $book->decrement('stock_available');

        $response = $this->get(route('catalog.index', ['tersedia' => '1']));

        $response->assertOk();
        $this->assertStringNotContainsString($book->title, $response->getContent());
    }

    public function test_detail_buku_menampilkan_informasi(): void
    {
        $this->seedDatabase();

        $book = Book::where('slug', 'bumi-manusia')->firstOrFail();

        $this->get(route('catalog.show', $book))
            ->assertOk()
            ->assertSee('Bumi Manusia')
            ->assertSee('Pramoedya Ananta Toer')
            ->assertSee('9789799731234');
    }

    public function test_detail_buku_dengan_slug_tidak_valid_404(): void
    {
        $this->get('/buku/tidak-ada-slug-ini')->assertNotFound();
    }

    public function test_admin_akan_lihat_dashboard_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Petugas');
    }

    public function test_anggota_lihat_riwayat_peminjaman(): void
    {
        $this->seedDatabase();
        $member = User::where('email', 'budi@perpustakaan.test')->firstOrFail();

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Peminjaman Saya')
            ->assertSee($member->loans->first()->book->title);
    }
}
