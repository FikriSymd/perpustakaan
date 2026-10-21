<?php

use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\LoanController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

// Katalog publik
Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/buku/{book:slug}', [CatalogController::class, 'show'])->name('catalog.show');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Reservasi sisi anggota
    Route::post('/buku/{book:slug}/reservasi', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservasi', [ReservationController::class, 'index'])->name('reservations.index');
    Route::delete('/reservasi/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
});

// Area admin (petugas perpustakaan)
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('categories', CategoryController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::resource('books', BookController::class)
            ->except(['show']);

        Route::get('loans', [LoanController::class, 'index'])->name('loans.index');
        Route::get('loans/create', [LoanController::class, 'create'])->name('loans.create');
        Route::post('loans', [LoanController::class, 'store'])->name('loans.store');
        Route::patch('loans/{loan}/return', [LoanController::class, 'return'])->name('loans.return');

        // Reservasi sisi admin
        Route::get('reservations', [AdminReservationController::class, 'index'])->name('reservations.index');
        Route::patch('reservations/{reservation}/fulfill', [AdminReservationController::class, 'fulfill'])->name('reservations.fulfill');
        Route::delete('reservations/{reservation}', [AdminReservationController::class, 'destroy'])->name('reservations.destroy');

        // Laporan
        Route::get('laporan', [ReportController::class, 'index'])->name('reports.index');
        Route::get('laporan/ekspor', [ReportController::class, 'export'])->name('reports.export');
    });

require __DIR__.'/auth.php';
