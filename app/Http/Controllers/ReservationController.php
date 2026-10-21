<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\Request;

/**
 * Reservasi sisi anggota.
 */
class ReservationController extends Controller
{
    public function __construct(private ReservationService $reservations)
    {
    }

    public function store(Request $request, Book $book)
    {
        [$reservation, $error] = $this->reservations->reserve($book, $request->user()->id);

        if ($error) {
            return back()->with('error', $error);
        }

        $message = $reservation->isReady()
            ? 'Reservasi dibuat — buku tersedia dan ditahan untuk Anda sampai '
                .$reservation->expires_at->translatedFormat('d F Y').'.'
            : 'Reservasi dibuat — Anda nomor antrean ke-'.$reservation->queue_position
                .' untuk buku ini. Kami akan mengabari lewat email saat bukunya siap.';

        return back()->with('success', $message);
    }

    public function index(Request $request)
    {
        $reservations = $request->user()
            ->reservations()
            ->with(['book.category'])
            ->latest()
            ->paginate(10);

        return view('member.reservations', [
            'reservations' => $reservations,
        ]);
    }

    public function destroy(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->user_id === $request->user()->id, 403);

        if (! $reservation->isOpen()) {
            return back()->with('error', 'Reservasi ini sudah selesai dan tidak bisa dibatalkan.');
        }

        $book = $reservation->book;
        $wasReady = $reservation->isReady();

        $reservation->update(['status' => Reservation::STATUS_DIBATALKAN, 'queue_position' => null]);

        // Bila tahanan batal, buku bisa langsung ditawarkan ke anggota berikutnya.
        if ($wasReady) {
            $this->reservations->promoteNext($book);
        }

        return back()->with('success', 'Reservasi dibatalkan.');
    }
}
