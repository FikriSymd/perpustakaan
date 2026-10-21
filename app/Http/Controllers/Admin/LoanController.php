<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function __construct(private ReservationService $reservations)
    {
    }

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $loans = Loan::query()
            ->with(['book:id,title,slug', 'user:id,name,member_code'])
            ->when($status === Loan::STATUS_DIPINJAM, fn ($q) => $q->active())
            ->when($status === 'terlambat', fn ($q) => $q->overdue())
            ->when($status === Loan::STATUS_DIKEMBALIKAN, fn ($q) => $q->where('status', Loan::STATUS_DIKEMBALIKAN))
            ->latest('borrowed_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'dipinjam' => Loan::active()->count(),
            'terlambat' => Loan::overdue()->count(),
        ];

        return view('admin.loans.index', [
            'loans' => $loans,
            'stats' => $stats,
            'currentStatus' => $status,
            'statuses' => [
                '' => 'Semua',
                Loan::STATUS_DIPINJAM => 'Sedang Dipinjam',
                'terlambat' => 'Terlambat',
                Loan::STATUS_DIKEMBALIKAN => 'Sudah Kembali',
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $members = User::query()
            ->where('role', User::ROLE_ANGGOTA)
            ->orderBy('name')
            ->get(['id', 'name', 'member_code']);

        $books = Book::query()
            ->with('category:id,name')
            ->available()
            ->orderBy('title')
            ->get(['id', 'title', 'author', 'stock_available', 'category_id']);

        $preselected = null;
        if ($bookId = $request->integer('book_id')) {
            $preselected = Book::find($bookId);
        }

        return view('admin.loans.create', [
            'members' => $members,
            'books' => $books,
            'preselected' => $preselected,
            'loanDays' => Loan::LOAN_DAYS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'book_id' => ['required', 'exists:books,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'user_id.required' => 'Pilih anggota peminjam.',
            'book_id.required' => 'Pilih buku yang dipinjam.',
        ]);

        $member = User::findOrFail($validated['user_id']);

        if (! $member->isAnggota()) {
            return back()->withInput()->with('error', 'Yang boleh meminjam hanya akun dengan peran anggota.');
        }

        $loan = DB::transaction(function () use ($validated, $member) {
            // Lock row buku supaya stok tidak bisa diperebutkan request paralel.
            $book = Book::whereKey($validated['book_id'])->lockForUpdate()->firstOrFail();

            if ($book->stock_available < 1) {
                return ['error' => "Stok \"{$book->title}\" habis, tidak dapat dipinjam."];
            }

            $activeCount = Loan::where('user_id', $member->id)
                ->active()
                ->count();

            if ($activeCount >= Loan::MAX_ACTIVE_LOANS) {
                return ['error' => "{$member->name} sudah mencapai batas ".Loan::MAX_ACTIVE_LOANS.' peminjaman aktif.'];
            }

            $book->decrement('stock_available');

            return Loan::create([
                'user_id' => $member->id,
                'book_id' => $book->id,
                'borrowed_at' => today(),
                'due_at' => today()->addDays(Loan::LOAN_DAYS),
                'status' => Loan::STATUS_DIPINJAM,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        if (is_array($loan)) {
            return back()->withInput()->with('error', $loan['error']);
        }

        return redirect()
            ->route('admin.loans.index')
            ->with('success', "Peminjaman \"{$loan->book->title}\" oleh {$member->name} tercatat. Kembali paling lambat ".$loan->due_at->translatedFormat('d F Y').'.');
    }

    public function return(Loan $loan)
    {
        if ($loan->status === Loan::STATUS_DIKEMBALIKAN) {
            return back()->with('error', 'Peminjaman ini sudah dikembalikan sebelumnya.');
        }

        DB::transaction(function () use ($loan) {
            $loan->update([
                'status' => Loan::STATUS_DIKEMBALIKAN,
                'returned_at' => today(),
                'fine_amount' => $loan->calculateFine(),
            ]);

            $loan->book()->lockForUpdate()->increment('stock_available');
        });

        // Stok balik → anggota paling depan di antrean reservasi dipromosikan.
        $promoted = $this->reservations->promoteNext($loan->book()->first());

        $fine = $loan->fine_amount;

        return redirect()
            ->route('admin.loans.index')
            ->with('success', "Buku \"{$loan->book->title}\" dikembalikan."
                .($fine > 0 ? ' Denda keterlambatan: Rp '.number_format($fine, 0, ',', '.').'.' : '')
                .($promoted ? ' Reservasi anggota berikutnya otomatis diaktifkan.' : ''));
    }
}
