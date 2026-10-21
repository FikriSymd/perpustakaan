<?php

namespace App\Models;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Book extends Model
{
    /** @use HasFactory<\Database\Factories\BookFactory> */
    use HasFactory;
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'author',
        'publisher',
        'publication_year',
        'isbn',
        'stock_total',
        'stock_available',
        'cover_path',
        'description',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function activeLoans(): HasMany
    {
        return $this->hasMany(Loan::class)->where('status', 'dipinjam');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function (Builder $q) use ($term) {
            $q->where(function (Builder $inner) use ($term) {
                $inner->where('title', 'like', "%{$term}%")
                    ->orWhere('author', 'like', "%{$term}%")
                    ->orWhere('isbn', 'like', "%{$term}%");
            });
        });
    }

    public function scopeInCategory(Builder $query, ?Category $category): Builder
    {
        return $query->when($category, fn (Builder $q) => $q->where('category_id', $category->id));
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('stock_available', '>', 0);
    }

    /**
     * Stok yang benar-benar bebas: eksemplar tersedia dikurangi reservasi
     * yang sedang menahan buku (status "tersedia").
     */
    public function hasStock(): bool
    {
        return $this->free_stock > 0;
    }

    /** Atribut computed: stok tersedia minus tahanan reservasi aktif. */
    public function getFreeStockAttribute(): int
    {
        // Hindari query berulang saat relasi sudah dimuat.
        if ($this->relationLoaded('reservations')) {
            $holds = $this->reservations->where('status', Reservation::STATUS_TERSEDIA)->count();
        } else {
            $holds = $this->reservations()->ready()->count();
        }

        return max(0, $this->stock_available - $holds);
    }

    protected static function booted(): void
    {
        static::creating(function (Book $book) {
            if (blank($book->slug)) {
                $book->slug = static::uniqueSlug($book->title);
            }
        });

        static::updating(function (Book $book) {
            if ($book->isDirty('title') && blank($book->slug)) {
                $book->slug = static::uniqueSlug($book->title);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'buku';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn (Builder $q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
