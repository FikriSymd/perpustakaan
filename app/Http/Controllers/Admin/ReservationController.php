<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\Request;

/**
 * Reservasi sisi admin: pantau antrean, tandai diambil, batalkan.
 */
class ReservationController extends Controller
{
    public function __construct(private ReservationService $reservations)
    {
    }

    public function index(Request $request)
    {
        $filter = $request->string('status')->toString();

        $reservations = Reservation::query()
            ->with(['user', 'book.category'])
            ->when($filter !== '', fn ($q) => $q->where('status', $filter))
            ->when($filter === '', fn ($q) => $q->open())
            ->orderByRaw("CASE status WHEN 'tersedia' THEN 0 WHEN 'menunggu' THEN 1 ELSE 2 END")
            ->orderBy('expires_at')
            ->orderBy('created_at')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'tersedia' => Reservation::ready()->count(),
            'menunggu' => Reservation::waiting()->count(),
        ];

        return view('admin.reservations.index', [
            'reservations' => $reservations,
            'filter' => $filter,
            'counts' => $counts,
        ]);
    }

    /** Buku diambil anggota → reservasi selesai + buat pinjaman baru. */
    public function fulfill(Request $request, Reservation $reservation)
    {
        if (! $reservation->isReady()) {
            return back()->with('error', 'Hanya reservasi berstatus "Siap Diambil" yang bisa diproses.');
        }

        $user = $reservation->user;
        $book = $reservation->book;

        // Reservasi ini sendiri termasuk "tahanan", jadi cek stok mentah:
        // reservasi akan dikonsumsi bersamaan dengan pengurangan stok di bawah.
        if ($book->stock_available < 1) {
            return back()->with('error', 'Stok buku habis — reservasi tidak bisa diproses.');
        }

        $activeCount = Loan::where('user_id', $user->id)->active()->count();
        if ($activeCount >= Loan::MAX_ACTIVE_LOANS) {
            return back()->with('error', $user->name.' sudah mencapai batas '.Loan::MAX_ACTIVE_LOANS.' pinjaman aktif.');
        }

        $dueAt = now()->addDays(Loan::LOAN_DAYS);

        Loan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'borrowed_at' => now(),
            'due_at' => $dueAt,
            'status' => Loan::STATUS_DIPINJAM,
        ]);

        $book->decrement('stock_available');

        $this->reservations->fulfill($reservation);

        return back()->with(
            'success',
            'Reservasi diproses — "'.$book->title.'" dipinjamkan ke '.$user->name
            .', jatuh tempo '.$dueAt->translatedFormat('d F Y').'.'
        );
    }

    public function destroy(Request $request, Reservation $reservation)
    {
        if (! $reservation->isOpen()) {
            return back()->with('error', 'Reservasi ini sudah selesai.');
        }

        $book = $reservation->book;
        $wasReady = $reservation->isReady();

        $reservation->update(['status' => Reservation::STATUS_DIBATALKAN, 'queue_position' => null]);

        if ($wasReady) {
            $this->reservations->promoteNext($book);
        }

        return back()->with('success', 'Reservasi dibatalkan.');
    }
}
