<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_mass_assignment_tidak_bisa_mengubah_role(): void
    {
        // Simulasi user mencoba menyuntik kolom role lewat request profil
        $member = User::factory()->anggota()->create();

        $response = $this->actingAs($member)
            ->patch(route('profile.update'), [
                'name' => 'Nama Baru',
                'email' => $member->email,
                'role' => User::ROLE_ADMIN,
                'member_code' => 'ADM-HACK',
            ]);

        $member->refresh();
        $this->assertSame('Nama Baru', $member->name);
        $this->assertSame(User::ROLE_ANGGOTA, $member->role, 'Role harus tidak berubah via mass assignment');
        $this->assertNotSame('ADM-HACK', $member->member_code);
    }

    public function test_admin_tidak_bisa_menyuntik_role_via_form_buku(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::create(['name' => 'Fiksi']);

        $this->actingAs($admin)
            ->post(route('admin.books.store'), [
                'category_id' => $category->id,
                'title' => 'Buku',
                'author' => 'Penulis',
                'stock_total' => 1,
                'user_id' => $admin->id,
            ])
            ->assertRedirect();

        $book = Book::where('title', 'Buku')->firstOrFail();
        $this->assertArrayNotHasKey('user_id', $book->getAttributes());
    }

    public function test_xss_di_judul_buku_tidak_eksekusi(): void
    {
        $category = Category::create(['name' => 'Fiksi']);
        // Blade escaping otomatis: judul dengan HTML harus tampil sebagai teks
        $book = Book::create([
            'category_id' => $category->id,
            'title' => '<script>alert(1)</script>',
            'author' => 'Penulis <b>tebal</b>',
            'stock_total' => 1,
            'stock_available' => 1,
        ]);

        $response = $this->get(route('catalog.show', $book));

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $content);
        $this->assertStringContainsString('&lt;script&gt;', $content);
    }

    public function test_sql_injection_di_pencarian_aman(): void
    {
        // Factory bisa membuat kategori sendiri (slug unik); pakai firstOrCreate agar tidak bentrok.
        $book = Book::factory()->create(['title' => 'Normal Book']);

        $response = $this->get(route('catalog.index', ['q' => "'; DROP TABLE books; --"]));

        $response->assertOk();
        $this->assertDatabaseHas('books', ['title' => 'Normal Book']);
    }
}
