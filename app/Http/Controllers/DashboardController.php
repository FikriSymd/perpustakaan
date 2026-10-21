<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return view('admin.dashboard', [
                'totalBooks' => Book::count(),
                'totalTitles' => Book::query()->selectRaw('COUNT(DISTINCT title) as c')->value('c'),
                'lowStock' => Book::query()->where('stock_available', '<=', 1)->count(),
                'activeLoans' => Loan::active()->count(),
                'overdueLoans' => Loan::overdue()->count(),
                'recentLoans' => Loan::query()
                    ->with(['book:id,title', 'user:id,name'])
                    ->latest('created_at')
                    ->limit(5)
                    ->get(),
            ]);
        }

        $loans = $user->loans()
            ->with('book:id,title,slug,author')
            ->latest('borrowed_at')
            ->latest('id')
            ->paginate(10);

        return view('member.dashboard', [
            'loans' => $loans,
            'activeCount' => $user->activeLoans()->count(),
        ]);
    }
}
