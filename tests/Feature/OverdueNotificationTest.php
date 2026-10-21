<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use App\Notifications\OverdueLoanNotification;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OverdueNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_perintah_mengirim_pengingat_untuk_pinjaman_terlambat(): void
    {
        $this->seed(DatabaseSeeder::class);
        Notification::fake();

        Loan::query()->delete();

        // Pastikan ada pinjaman terlambat.
        $member = User::where('email', 'siti@perpustakaan.test')->firstOrFail();
        $book = Book::where('stock_available', '>', 0)->firstOrFail();

        $loan = Loan::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'borrowed_at' => now()->subDays(20),
            'due_at' => now()->subDays(6),
            'status' => Loan::STATUS_DIPINJAM,
        ]);

        $this->artisan('library:notify-overdue')->assertSuccessful();

        Notification::assertSentTo($member, OverdueLoanNotification::class);

        $this->assertNotNull($loan->fresh()->overdue_notified_at);
    }

    public function test_tidak_mengirim_ulang_bila_sudah_dinotifikasi(): void
    {
        $this->seed(DatabaseSeeder::class);
        Notification::fake();

        // Mulai dari kondisi bersih agar deterministik.
        Loan::query()->delete();

        $member = User::where('email', 'siti@perpustakaan.test')->firstOrFail();
        $book = Book::where('stock_available', '>', 0)->firstOrFail();

        Loan::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'borrowed_at' => now()->subDays(20),
            'due_at' => now()->subDays(6),
            'status' => Loan::STATUS_DIPINJAM,
            'overdue_notified_at' => now()->subDay(),
        ]);

        $this->artisan('library:notify-overdue')->assertSuccessful();

        Notification::assertNotSentTo($member, OverdueLoanNotification::class);
    }

    public function test_pinjaman_belum_terlambat_tidak_dinotifikasi(): void
    {
        $this->seed(DatabaseSeeder::class);
        Notification::fake();

        $member = User::where('email', 'budi@perpustakaan.test')->firstOrFail();
        $book = Book::where('stock_available', '>', 0)->firstOrFail();

        Loan::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(14),
            'status' => Loan::STATUS_DIPINJAM,
        ]);

        $this->artisan('library:notify-overdue')->assertSuccessful();

        Notification::assertNothingSentTo($member);
    }

    public function test_dry_run_tidak_mengirim_email(): void
    {
        $this->seed(DatabaseSeeder::class);
        Notification::fake();

        Loan::query()->delete();

        $member = User::where('email', 'siti@perpustakaan.test')->firstOrFail();
        $book = Book::where('stock_available', '>', 0)->firstOrFail();

        Loan::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'borrowed_at' => now()->subDays(20),
            'due_at' => now()->subDays(6),
            'status' => Loan::STATUS_DIPINJAM,
        ]);

        $this->artisan('library:notify-overdue', ['--dry-run' => true])->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_jadwal_harian_terdaftar(): void
    {
        $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);
        $events = collect($schedule->events())->map->command ?? collect();

        $hasEvent = collect($schedule->events())
            ->contains(fn ($event) => str_contains($event->command ?? '', 'library:notify-overdue'));

        $this->assertTrue($hasEvent, 'Perintah library:notify-overdue harus terjadwal harian.');
    }
}
