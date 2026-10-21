<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_dapat_menambahkan_kategori(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), ['name' => 'Cerpen'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', ['name' => 'Cerpen', 'slug' => 'cerpen']);
    }

    public function test_nama_kategori_harus_unik(): void
    {
        Category::create(['name' => 'Fiksi']);

        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), ['name' => 'Fiksi'])
            ->assertSessionHasErrors('name');
    }

    public function test_kategori_dengan_buku_tidak_bisa_dihapus(): void
    {
        $category = Category::create(['name' => 'Fiksi']);
        Book::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_kategori_kosong_bisa_dihapus(): void
    {
        $category = Category::create(['name' => 'Kosong']);

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_anggota_tidak_bisa_mengelola_kategori(): void
    {
        $member = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->post(route('admin.categories.store'), ['name' => 'Hacked'])
            ->assertForbidden();

        $this->assertDatabaseMissing('categories', ['name' => 'Hacked']);
    }
}
