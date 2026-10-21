<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    public const STATUS_DIPINJAM = 'dipinjam';
    public const STATUS_DIKEMBALIKAN = 'dikembalikan';

    /** Denda per hari keterlambatan (Rupiah). */
    public const FINE_PER_DAY = 1000;

    /** Lama peminjaman standar (hari). */
    public const LOAN_DAYS = 14;

    /** Batas maksimal buku yang boleh dipinjam aktif per anggota. */
    public const MAX_ACTIVE_LOANS = 3;

    protected $fillable = [
        'user_id',
        'book_id',
        'borrowed_at',
        'due_at',
        'returned_at',
        'fine_amount',
        'status',
        'notes',
        'overdue_notified_at',
    ];

    protected $attributes = [
        'status' => self::STATUS_DIPINJAM,
        'fine_amount' => 0,
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'date',
            'due_at' => 'date',
            'returned_at' => 'date',
            'fine_amount' => 'integer',
            'overdue_notified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_DIPINJAM && $this->due_at->isPast();
    }

    public function overdueDays(): int
    {
        if (! $this->isOverdue()) {
            return 0;
        }

        return max(1, (int) $this->due_at->diffInDays(today()));
    }

    public function calculateFine(): int
    {
        if ($this->status === self::STATUS_DIKEMBALIKAN) {
            return $this->fine_amount;
        }

        return $this->overdueDays() * self::FINE_PER_DAY;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DIPINJAM);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->active()->whereDate('due_at', '<', today());
    }
}
