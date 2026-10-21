<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\ReservationReadyNotification;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    private function member(): User
    {
        return User::where('email', 'budi@perpustakaan.test')->firstOrFail();
    }

    private function otherMember(): User
    {
        return User::where('email', 'siti@perpustakaan.test')->firstOrFail();
    }

    /**
     * Buku dengan stok cukup, tanpa reservasi terbuka DAN tanpa pinjaman aktif
     * dari anggota tsb — supaya test tidak bentrok dengan data demo seeder.
     */
    private function bookWithoutReservationFor(User $member, int $minStock = 2): Book
    {
        return Book::where('stock_available', '>=', $minStock)
            ->whereDoesntHave('reservations', fn ($q) => $q->where('user_id', $member->id)->open())
            ->whereDoesntHave('loans', fn ($q) => $q->where('user_id', $member->id)->where('status', Loan::STATUS_DIPINJAM))
            ->firstOrFail();
    }

    public function test_anggota_bisa_reservasi_buku_yang_tersedia(): void
    {
        $this->seed(DatabaseSeeder::class);
        Notification::fake();

        $member = $this->member();
        $book = $this->bookWithoutReservationFor($member, minStock: 2);

        $this->actingAs($member)
            ->post(route('reservations.store', $book))
            ->assertRedirect();

        $this->assertDatabaseHas('reservations', [
            'user_id' => $member->id,
            'book_id' => $book->id,
            'status' => Reservation::STATUS_TERSEDIA,
        ]);

        Notification::assertSentTo($member, ReservationReadyNotification::class);
    }

    public function test_buku_habis_membuat_reservasi_menunggu_antrean(): void
    {
        $this->seed(DatabaseSeeder::class);
        Notification::fake();

        // Mulai bersih agar tidak bentrok dengan data demo seeder.
        Reservation::query()->delete();
        Loan::query()->delete();

        $member = $this->member();

        // Habiskan stok sebuah buku.
        $book = Book::where('stock_available', '>', 0)->firstOrFail();
        $book->update(['stock_available' => 0]);

        $this->actingAs($member)
            ->post(route('reservations.store', $book))
            ->assertRedirect();

        $this->assertDatabaseHas('reservations', [
            'user_id' => $member->id,
            'book_id' => $book->id,
            'status' => Reservation::STATUS_MENUNGGU,
        ]);

        $this->assertSame(1, Reservation::where('book_id', $book->id)->waiting()->count());
    }

    public function test_anggota_tidak_bisa_reservasi_ganda(): void
    {
        $this->seed(DatabaseSeeder::class);
        Notification::fake();

        $book = $this->bookWithoutReservationFor($this->member());
        $member = $this->member();

        $this->actingAs($member)->post(route('reservations.store', $book))->assertRedirect();
        $this->actingAs($member)->post(route('reservations.store', $book))->assertRedirect();

        $this->assertSame(1, Reservation::where('user_id', $member->id)->where('book_id', $book->id)->count());
    }

    public function test_pengembalian_buku_mempromosikan_antrean_berikutnya(): void
    {
        $this->seed(DatabaseSeeder::class);
        Notification::fake();

        // Mulai bersih agar tidak bentrok dengan data demo seeder.
        Reservation::query()->delete();
        Loan::query()->delete();

        $book = Book::where('stock_available', '>', 0)->firstOrFail();
        $book->update(['stock_available' => 0]);

        // Dua anggota mengantre.
        $first = $this->member();
        $second = $this->otherMember();

        $this->actingAs($first)->post(route('reservations.store', $book))->assertRedirect();
        $this->actingAs($second)->post(route('reservations.store', $book))->assertRedirect();

        $this->assertSame(2, Reservation::where('book_id', $book->id)->waiting()->count());

        // Stok kembali → promosikan yang paling depan (first).
        $book->refresh()->update(['stock_available' => 1]);
        app(\App\Services\ReservationService::class)->promoteNext($book->refresh());

        $front = Reservation::where('user_id', $first->id)->where('book_id', $book->id)->firstOrFail();
        $back = Reservation::where('user_id', $second->id)->where('book_id', $book->id)->firstOrFail();

        $this->assertSame(Reservation::STATUS_TERSEDIA, $front->status);
        $this->assertNotNull($front->expires_at);
        $this->assertSame(Reservation::STATUS_MENUNGGU, $back->status);

        Notification::assertSentTo($first, ReservationReadyNotification::class);
    }

    public function test_admin_bisa_proses_pengambilan_reservasi(): void
    {
        $this->seed(DatabaseSeeder::class);
        Notification::fake();

        $admin = User::where('email', 'admin@perpustakaan.test')->firstOrFail();
        $member = $this->member();
        $book = $this->bookWithoutReservationFor($member);

        $this->actingAs($member)->post(route('reservations.store', $book))->assertRedirect();

        $reservation = Reservation::where('user_id', $member->id)
            ->where('book_id', $book->id)
            ->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.reservations.fulfill', $reservation))
            ->assertRedirect();

        $this->assertSame(Reservation::STATUS_DIAMBIL, $reservation->fresh()->status);

        $this->assertDatabaseHas('loans', [
            'user_id' => $member->id,
            'book_id' => $book->id,
            'status' => Loan::STATUS_DIPINJAM,
        ]);
    }

    public function test_anggota_bisa_membatalkan_reservasi_sendiri(): void
    {
        $this->seed(DatabaseSeeder::class);
        Notification::fake();

        $member = $this->member();
        $book = $this->bookWithoutReservationFor($member);

        $this->actingAs($member)->post(route('reservations.store', $book))->assertRedirect();

        $reservation = Reservation::where('user_id', $member->id)->where('book_id', $book->id)->firstOrFail();

        $this->actingAs($member)
            ->delete(route('reservations.destroy', $reservation))
            ->assertRedirect();

        $this->assertSame(Reservation::STATUS_DIBATALKAN, $reservation->fresh()->status);
    }

    public function test_anggota_lain_tidak_bisa_membatalkan_reservasi_orang(): void
    {
        $this->seed(DatabaseSeeder::class);
        Notification::fake();

        $book = $this->bookWithoutReservationFor($this->member());

        $this->actingAs($this->member())->post(route('reservations.store', $book))->assertRedirect();
        $reservation = Reservation::where('user_id', $this->member()->id)->where('book_id', $book->id)->firstOrFail();

        $this->actingAs($this->otherMember())
            ->delete(route('reservations.destroy', $reservation))
            ->assertForbidden();

        $this->assertTrue($reservation->fresh()->isOpen());
    }

    public function test_halaman_reservasi_member_menampilkan_antrean(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs($this->member())
            ->get(route('reservations.index'))
            ->assertOk()
            ->assertSee('Reservasi');
    }

    public function test_tamu_tidak_bisa_membuat_reservasi(): void
    {
        $this->seed(DatabaseSeeder::class);

        $book = Book::firstOrFail();

        $this->post(route('reservations.store', $book))->assertRedirect(route('login'));
    }
}
