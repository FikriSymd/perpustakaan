<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Reservation;
use App\Notifications\ReservationReadyNotification;
use Illuminate\Support\Facades\DB;

/**
 * Logika antrean reservasi terpusat.
 *
 * Aturan:
 *  - Satu anggota hanya boleh punya satu reservasi terbuka per judul.
 *  - Saat stok kosong, reservasi masuk antrean "menunggu".
 *  - Saat stok tersedia, reservasi langsung "tersedia" + ditahan HOLD_DAYS hari.
 *  - Saat buku dikembalikan, anggota paling depan di antrean otomatis dipromosikan.
 */
class ReservationService
{
    /**
     * Buat reservasi baru. Mengembalikan [reservation, error].
     *
     * @return array{0: ?Reservation, 1: ?string}
     */
    public function reserve(Book $book, int $userId, ?string $notes = null): array
    {
        return DB::transaction(function () use ($book, $userId, $notes) {
            $book = Book::whereKey($book->id)->lockForUpdate()->firstOrFail();

            $existing = Reservation::where('user_id', $userId)
                ->where('book_id', $book->id)
                ->open()
                ->exists();

            if ($existing) {
                return [null, 'Anda sudah punya reservasi aktif untuk buku ini.'];
            }

            // Tidak masuk akal memesan buku yang sedang dipegang sendiri.
            $alreadyHolding = $book->loans()
                ->where('user_id', $userId)
                ->where('status', \App\Models\Loan::STATUS_DIPINJAM)
                ->exists();

            if ($alreadyHolding) {
                return [null, 'Anda sedang meminjam buku ini.'];
            }

            $reservation = Reservation::create([
                'user_id' => $userId,
                'book_id' => $book->id,
                'status' => Reservation::STATUS_MENUNGGU,
                'notes' => $notes,
            ]);

            if ($book->hasStock()) {
                $this->markReady($reservation, $book);
            } else {
                $reservation->update(['queue_position' => $this->nextQueuePosition($book)]);
            }

            return [$reservation->fresh(), null];
        });
    }

    /**
     * Promosikan anggota paling depan di antrean sebuah buku (dipanggil
     * setelah stok bertambah, mis. saat pengembalian).
     */
    public function promoteNext(Book $book): ?Reservation
    {
        if (! $book->hasStock()) {
            return null;
        }

        $next = Reservation::where('book_id', $book->id)
            ->waiting()
            ->queued()
            ->first();

        if (! $next) {
            return null;
        }

        $this->markReady($next, $book);

        return $next->fresh();
    }

    /** Tandai reservasi siap diambil + kirim notifikasi email. */
    public function markReady(Reservation $reservation, Book $book): void
    {
        $reservation->update([
            'status' => Reservation::STATUS_TERSEDIA,
            'queue_position' => null,
            'notified_at' => now(),
            'expires_at' => now()->addDays(Reservation::HOLD_DAYS),
        ]);

        $reservation->user->notify(new ReservationReadyNotification($reservation->fresh()));
    }

    /** Reservasi yang tahanannya sudah lewat waktu → tandai kadaluarsa. */
    public function expireStale(): int
    {
        $stale = Reservation::ready()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($stale as $reservation) {
            $reservation->update(['status' => Reservation::STATUS_KADALUARSA]);
        }

        return $stale->count();
    }

    /** Tandai reservasi selesai (buku sudah diambil anggota). */
    public function fulfill(Reservation $reservation): void
    {
        $reservation->update([
            'status' => Reservation::STATUS_DIAMBIL,
            'queue_position' => null,
        ]);
    }

    private function nextQueuePosition(Book $book): int
    {
        return (int) Reservation::where('book_id', $book->id)->waiting()->max('queue_position') + 1;
    }
}
