<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_TERSEDIA = 'tersedia';
    public const STATUS_DIAMBIL = 'diambil';
    public const STATUS_DIBATALKAN = 'dibatalkan';
    public const STATUS_KADALUARSA = 'kadaluarsa';

    /** Berapa hari buku yang sudah siap ditahan untuk anggota sebelum hangus. */
    public const HOLD_DAYS = 3;

    protected $fillable = [
        'user_id',
        'book_id',
        'status',
        'queue_position',
        'notified_at',
        'expires_at',
        'notes',
    ];

    protected $attributes = [
        'status' => self::STATUS_MENUNGGU,
    ];

    protected function casts(): array
    {
        return [
            'notified_at' => 'datetime',
            'expires_at' => 'datetime',
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

    public function isWaiting(): bool
    {
        return $this->status === self::STATUS_MENUNGGU;
    }

    public function isReady(): bool
    {
        return $this->status === self::STATUS_TERSEDIA;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_MENUNGGU, self::STATUS_TERSEDIA], true);
    }

    /** Sisa hari sebelum tahanan buku hangus (null bila belum siap). */
    public function holdDaysLeft(): ?int
    {
        if (! $this->isReady() || ! $this->expires_at) {
            return null;
        }

        return max(0, (int) today()->diffInDays($this->expires_at, false));
    }

    public function isExpired(): bool
    {
        return $this->isReady() && $this->expires_at && $this->expires_at->isPast();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU => 'Menunggu',
            self::STATUS_TERSEDIA => 'Siap Diambil',
            self::STATUS_DIAMBIL => 'Sudah Diambil',
            self::STATUS_DIBATALKAN => 'Dibatalkan',
            self::STATUS_KADALUARSA => 'Kadaluarsa',
            default => $this->status,
        };
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_MENUNGGU, self::STATUS_TERSEDIA]);
    }

    public function scopeWaiting(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_MENUNGGU);
    }

    public function scopeReady(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_TERSEDIA);
    }

    /** Urutan antrean: paling awal menunggu di depan. */
    public function scopeQueued(Builder $query): Builder
    {
        return $query->orderBy('created_at')->orderBy('id');
    }
}
