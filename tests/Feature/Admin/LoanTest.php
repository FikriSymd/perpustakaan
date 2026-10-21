<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $member;

    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->member = User::factory()->anggota()->create();
        $this->book = Book::factory()->create(['stock_total' => 3, 'stock_available' => 3]);
    }

    public function test_admin_dapat_mencatat_peminjaman(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.loans.store'), [
                'user_id' => $this->member->id,
                'book_id' => $this->book->id,
            ]);

        $response->assertRedirect(route('admin.loans.index'));

        $loan = Loan::where('user_id', $this->member->id)->where('book_id', $this->book->id)->firstOrFail();
        $this->assertSame(Loan::STATUS_DIPINJAM, $loan->status);
        $this->assertSame(14, (int) $loan->borrowed_at->diffInDays($loan->due_at));

        $this->book->refresh();
        $this->assertSame(2, $this->book->stock_available);
    }

    public function test_stok_habis_tidak_bisa_dipinjam(): void
    {
        $this->book->update(['stock_available' => 0]);

        $this->actingAs($this->admin)
            ->post(route('admin.loans.store'), [
                'user_id' => $this->member->id,
                'book_id' => $this->book->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('loans', ['book_id' => $this->book->id]);
        $this->book->refresh();
        $this->assertSame(0, $this->book->stock_available);
    }

    public function test_anggota_dibatasi_maksimal_tiga_peminjaman_aktif(): void
    {
        for ($i = 0; $i < Loan::MAX_ACTIVE_LOANS; $i++) {
            Loan::create([
                'user_id' => $this->member->id,
                'book_id' => Book::factory()->create()->id,
                'borrowed_at' => today(),
                'due_at' => today()->addDays(14),
                'status' => Loan::STATUS_DIPINJAM,
            ]);
        }

        $this->actingAs($this->admin)
            ->post(route('admin.loans.store'), [
                'user_id' => $this->member->id,
                'book_id' => $this->book->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('loans', ['book_id' => $this->book->id, 'user_id' => $this->member->id]);
    }

    public function test_pengembalian_mengembalikan_stok(): void
    {
        $loan = Loan::create([
            'user_id' => $this->member->id,
            'book_id' => $this->book->id,
            'borrowed_at' => today()->subDays(3),
            'due_at' => today()->addDays(11),
            'status' => Loan::STATUS_DIPINJAM,
        ]);
        $this->book->decrement('stock_available');

        $this->actingAs($this->admin)
            ->patch(route('admin.loans.return', $loan))
            ->assertRedirect();

        $loan->refresh();
        $this->assertSame(Loan::STATUS_DIKEMBALIKAN, $loan->status);
        $this->assertSame(0, $loan->fine_amount);
        $this->assertNotNull($loan->returned_at);

        $this->book->refresh();
        $this->assertSame(3, $this->book->stock_available);
    }

    public function test_pengembalian_terlambat_kena_denda(): void
    {
        $loan = Loan::create([
            'user_id' => $this->member->id,
            'book_id' => $this->book->id,
            'borrowed_at' => today()->subDays(20),
            'due_at' => today()->subDays(6),
            'status' => Loan::STATUS_DIPINJAM,
        ]);
        $this->book->decrement('stock_available');

        $this->actingAs($this->admin)
            ->patch(route('admin.loans.return', $loan))
            ->assertRedirect();

        $loan->refresh();
        $this->assertSame(6, $loan->fine_amount / Loan::FINE_PER_DAY);
    }

    public function test_pengembalian_ganda_ditolak(): void
    {
        $loan = Loan::create([
            'user_id' => $this->member->id,
            'book_id' => $this->book->id,
            'borrowed_at' => today()->subDays(3),
            'due_at' => today()->addDays(11),
            'status' => Loan::STATUS_DIKEMBALIKAN,
            'returned_at' => today()->subDays(1),
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.loans.return', $loan))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_anggota_tidak_bisa_mencatat_peminjaman(): void
    {
        $this->actingAs($this->member)
            ->post(route('admin.loans.store'), [
                'user_id' => $this->member->id,
                'book_id' => $this->book->id,
            ])
            ->assertForbidden();
    }
}
