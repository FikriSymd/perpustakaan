<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::where('email', 'admin@perpustakaan.test')->firstOrFail();
    }

    private function member(): User
    {
        return User::where('email', 'budi@perpustakaan.test')->firstOrFail();
    }

    public function test_admin_bisa_membuka_halaman_laporan(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs($this->admin())
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Laporan');
    }

    public function test_anggota_tidak_bisa_membuka_laporan(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs($this->member())
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }

    public function test_ekspor_peminjaman_menghasilkan_csv(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.reports.export', ['jenis' => 'peminjaman']));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Anggota', $response->streamedContent());
    }

    public function test_ekspor_buku_menghasilkan_csv(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.reports.export', ['jenis' => 'buku']));

        $response->assertOk();
        $this->assertStringContainsString('Judul', $response->streamedContent());
    }

    public function test_ekspor_reservasi_menghasilkan_csv(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.reports.export', ['jenis' => 'reservasi']));

        $response->assertOk();
        $this->assertStringContainsString('Reservasi', $response->streamedContent());
    }

    public function test_jenis_ekspor_tidak_valid_ditolak(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs($this->admin())
            ->get(route('admin.reports.export', ['jenis' => 'ngawur']))
            ->assertSessionHasErrors('jenis');
    }

    public function test_filter_tanggal_diterapkan(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.reports.export', [
                'jenis' => 'peminjaman',
                'dari' => now()->subYears(5)->format('Y-m-d'),
                'sampai' => now()->format('Y-m-d'),
            ]));

        $response->assertOk();
        $this->assertStringContainsString('Anggota', $response->streamedContent());
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
    }
}
