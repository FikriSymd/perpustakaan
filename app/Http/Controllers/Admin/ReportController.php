<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Laporan sirkulasi & koleksi — diekspor sebagai CSV.
 *
 * Dataset: peminjaman (dengan filter rentang tanggal & status), koleksi buku,
 * dan reservasi. Semuanya di-stream supaya hemat memori untuk data besar.
 */
class ReportController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total_books' => Book::count(),
            'total_copies' => (int) Book::sum('stock_total'),
            'available_copies' => (int) Book::sum('stock_available'),
            'active_loans' => Loan::active()->count(),
            'overdue_loans' => Loan::overdue()->count(),
            'open_reservations' => Reservation::open()->count(),
            'ready_reservations' => Reservation::ready()->count(),
            'members' => \App\Models\User::where('role', \App\Models\User::ROLE_ANGGOTA)->count(),
        ];

        return view('admin.reports.index', [
            'stats' => $stats,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'jenis' => ['required', 'in:peminjaman,buku,reservasi'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'status' => ['nullable', 'string'],
        ]);

        $jenis = $validated['jenis'];
        $dari = isset($validated['dari']) ? Carbon::parse($validated['dari'])->startOfDay() : null;
        $sampai = isset($validated['sampai']) ? Carbon::parse($validated['sampai'])->endOfDay() : null;
        $status = $validated['status'] ?? null;

        $stamp = now()->format('Ymd_His');
        $filename = "laporan-{$jenis}-{$stamp}.csv";

        $query = match ($jenis) {
            'peminjaman' => Loan::query()->with(['user', 'book.category']),
            'buku' => Book::query()->with('category'),
            'reservasi' => Reservation::query()->with(['user', 'book.category']),
        };

        // Filter rentang tanggal memakai kolom tanggal yang relevan per dataset.
        if ($dari && $sampai) {
            $column = match ($jenis) {
                'peminjaman' => 'borrowed_at',
                'reservasi' => 'created_at',
                default => 'created_at',
            };
            $query->whereBetween($column, [$dari, $sampai]);
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $headers = $this->headersFor($jenis);
        $rows = $this->rowMapper($jenis);

        return response()->streamDownload(function () use ($query, $headers, $rows) {
            $out = fopen('php://output', 'w');

            // BOM agar Excel membaca UTF-8 dengan benar.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);

            $query->chunkById(500, function ($records) use ($out, $rows) {
                foreach ($records as $record) {
                    fputcsv($out, $rows($record));
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function headersFor(string $jenis): array
    {
        return match ($jenis) {
            'peminjaman' => [
                'ID', 'Anggota', 'No. Anggota', 'Buku', 'Kategori', 'Tanggal Pinjam',
                'Jatuh Tempo', 'Dikembalikan', 'Status', 'Terlambat (hari)', 'Denda (Rp)',
            ],
            'buku' => [
                'ID', 'Judul', 'Penulis', 'Penerbit', 'Tahun', 'Kategori',
                'Stok Total', 'Stok Tersedia', 'Dipinjam', 'Status',
            ],
            'reservasi' => [
                'ID', 'Anggota', 'No. Anggota', 'Buku', 'Kategori',
                'Tanggal Reservasi', 'Status', 'Notifikasi Terkirim',
            ],
        };
    }

    private function rowMapper(string $jenis): \Closure
    {
        return match ($jenis) {
            'peminjaman' => function (Loan $loan) {
                return [
                    $loan->id,
                    $loan->user->name,
                    $loan->user->member_code,
                    $loan->book->title,
                    $loan->book->category?->name,
                    optional($loan->borrowed_at)->format('Y-m-d'),
                    optional($loan->due_at)->format('Y-m-d'),
                    optional($loan->returned_at)->format('Y-m-d'),
                    $loan->status,
                    $loan->overdueDays(),
                    $loan->calculateFine(),
                ];
            },
            'buku' => function (Book $book) {
                return [
                    $book->id,
                    $book->title,
                    $book->author,
                    $book->publisher,
                    $book->publication_year,
                    $book->category?->name,
                    $book->stock_total,
                    $book->stock_available,
                    $book->stock_total - $book->stock_available,
                    $book->stock_available > 0 ? 'Tersedia' : 'Habis',
                ];
            },
            'reservasi' => function (Reservation $reservation) {
                return [
                    $reservation->id,
                    $reservation->user->name,
                    $reservation->user->member_code,
                    $reservation->book->title,
                    $reservation->book->category?->name,
                    $reservation->created_at->format('Y-m-d'),
                    $reservation->statusLabel(),
                    optional($reservation->notified_at)->format('Y-m-d H:i'),
                ];
            },
        };
    }
}
