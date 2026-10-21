<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Reservation;
use App\Notifications\OverdueLoanNotification;
use App\Services\ReservationService;
use Illuminate\Console\Command;

class NotifyOverdueCommand extends Command
{
    protected $signature = 'library:notify-overdue
                            {--dry-run : Tampilkan hasil tanpa mengirim email}
                            {--force : Kirim ulang walau sudah pernah dinotifikasi}';

    protected $description = 'Kirim email pengingat untuk pinjaman terlambat & kelola siklus reservasi harian';

    public function handle(ReservationService $reservations): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        // ── 1. Kadaluarsakan reservasi yang tahanannya lewat batas ──
        $expired = $dryRun
            ? Reservation::ready()->whereNotNull('expires_at')->where('expires_at', '<', now())->count()
            : $reservations->expireStale();

        // ── 2. Promosikan antrean reservasi untuk buku yang stoknya kembali tersedia ──
        $promoted = 0;
        if (! $dryRun) {
            Book::query()
                ->whereHas('reservations', fn ($q) => $q->waiting())
                ->chunkById(100, function ($books) use ($reservations, &$promoted) {
                    foreach ($books as $book) {
                        if ($reservations->promoteNext($book)) {
                            $promoted++;
                        }
                    }
                });
        }

        // ── 3. Email pengingat terlambat ──
        $loans = Loan::overdue()
            ->with(['user', 'book'])
            ->when(! $force, fn ($q) => $q->whereNull('overdue_notified_at'))
            ->whereHas('user', fn ($q) => $q->whereNotNull('email'))
            ->get();

        $notified = 0;
        foreach ($loans as $loan) {
            if ($dryRun) {
                $this->line("  [dry] {$loan->user->name} — {$loan->book->title} ({$loan->overdueDays()} hari, denda Rp ".number_format($loan->calculateFine(), 0, ',', '.').')');
                continue;
            }

            $loan->user->notify(new OverdueLoanNotification($loan));
            $loan->forceFill(['overdue_notified_at' => now()])->save();
            $notified++;
        }

        $this->info("Reservasi kadaluarsa: {$expired} · antrean dipromosikan: {$promoted} · pengingat: "
            .($dryRun ? count($loans).' (dry-run)' : $notified));

        return self::SUCCESS;
    }
}
