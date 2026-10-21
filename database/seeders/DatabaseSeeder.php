<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Koleksi awal: judul nyata, penulis nyata, supaya katalog tidak
     * terlihat seperti data lorem-ipsum.
     */
    private array $collection = [
        'Fiksi & Sastra' => [
            ['Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, '9789793062792', 5, 'Kisah sepuluh anak belajar di Muhammadiyah Belitung penuh kekurangan, tetapi tak pernah kehilangan harian.'],
            ['Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, '9789799731234', 4, 'Kisah Minke, pemuda pribumi terpelajar di penghujung abad ke-19, yang berhadapan dengan ketidakadilan kolonial.'],
            ['Cantik Itu Luka', 'Eka Kurniawan', 'Gramedia Pustaka Utama', 2002, '9789799104960', 3, 'Sejarah keluarga cantik di Halimunda yang tak pernah lepas dari cinta dan kekerasan sepanjang dua zaman.'],
            ['Hujan', 'Tere Liye', 'Republika', 2016, '9786029938515', 6, 'Persahabatan, cinta, dan kehilangan yang dibawa badai besar di desa tulip.'],
            ['Negeri 5 Menara', 'A. Fuadi', 'Bukune', 2009, '9786022510783', 4, 'Rano dan sahabatnya menempa ilmu agama dan hidup di pesantren dengan mimpi sebesar menara.'],
            ['Sang Pemimpi', 'Andrea Hirata', 'Bentang Pustaka', 2006, '9789793062938', 3, 'Lanjutan kisah Ikal dan Arai mengejar mimpi di perantauan.'],
        ],
        'Sains & Teknologi' => [
            ['A Brief History of Time', 'Stephen Hawking', 'Bantam Books', 1988, '9780553380163', 3, 'Penjelasan kosmologi modern: lubang hitam, big bang, dan waktu, dalam bahasa yang bisa dinikmati awam.'],
            ['Cosmos', 'Carl Sagan', 'Ballantine Books', 1980, '9780345539434', 2, 'Perjalanan menelusuri alam semesta dari Bumi hingga tepian galaksi.'],
            ['Clean Code', 'Robert C. Martin', 'Prentice Hall', 2008, '9780132350884', 4, 'Pedoman menulis kode yang mudah dibaca, dirawat, dan diandalkan oleh tim perangkat lunak.'],
            ['The Selfish Gene', 'Richard Dawkins', 'Oxford University Press', 1976, '9780198788607', 2, 'Evolusi dilihat dari sudut pandang gen sebagai unit seleksi.'],
            ['Sapiens: Riwayat Singkat Umat Manusia', 'Yuval Noah Harari', 'KPG', 2017, '9786028583017', 5, 'Sejarah ringkas Homo sapiens dari species yang tidak penting menjadi penguasa planet.'],
        ],
        'Sejarah & Budaya' => [
            ['The Geography of Thought', 'Richard E. Nisbett', 'Free Press', 2003, '9780743255356', 2, 'Bagaimana cara berpikir orang Timur dan Barat dibentuk oleh budaya dan lingkungannya.'],
            ['Sejarah Indonesia Modern', 'M.C. Ricklefs', 'Serambi', 2008, '9789790244627', 3, 'Kajian komprehensif sejarah Nusantara sejak 1300 hingga era Reformasi.'],
            ['Batik: Warisan Budaya Dunia', 'Iwan Tirta', 'Nadar Graphica', 1996, '9789798926002', 2, 'Makna filosofis dan pola-pola batik klasik Jawa.'],
        ],
        'Pengembangan Diri' => [
            ['Atomic Habits', 'James Clear', 'Penguin Random House', 2018, '9780735211292', 7, 'Cara membangun kebiasaan kecil yang berdampak besar melalui sistem, bukan sekadar motivasi.'],
            ['The 7 Habits of Highly Effective People', 'Stephen R. Covey', 'Free Press', 1989, '9780743269513', 3, 'Tujuh kebiasaan fundamental untuk efektivitas pribadi dan profesional.'],
            ['Filosofi Teras', 'Henry Manampiring', 'Kompas', 2018, '9786231650029', 6, 'Pengantar stoisisme untuk mental yang lebih kuat menghadapi ketidakpastian.'],
            ['Mindset', 'Carol S. Dweck', 'Ballantine Books', 2006, '9780345472328', 3, 'Psikologi growth mindset dan pengaruhnya terhadap pencapaian.'],
        ],
        'Anak & Remaja' => [
            ['Si Kancil dan Buaya', 'Kumpulan Dongeng Nusantara', 'Balai Pustaka', 2010, null, 5, 'Kumpulan dongeng hewan klasik Nusantara dengan pesan moral yang tak lekang oleh zaman.'],
            ['Kisah 25 Nabi dan Rasul', 'Tim Cahaya Hati', 'Erlangga', 2015, null, 4, 'Kisah para nabi untuk anak-anak, disajikan dengan bahasa sederhana.'],
            ['Percy Jackson: The Lightning Thief', 'Rick Riordan', 'Disney Hyperion', 2005, '9780786838653', 4, 'Percy menemukan bahwa ayahnya adalah dewa Yunani dan dunia mitologi ternyata nyata.'],
            ['Harry Potter and the Philosopher\'s Stone', 'J.K. Rowling', 'Bloomsbury', 1997, '9780747532699', 6, 'Awal perjalanan Harry di sekolah sihir Hogwarts.'],
        ],
    ];

    public function run(): void
    {
        // --- Akun inti ---
        User::create([
            'name' => 'Petugas Perpustakaan',
            'email' => 'admin@perpustakaan.test',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'member_code' => 'ADM-001',
        ]);

        $members = collect([
            ['Budi Santoso', 'budi@perpustakaan.test', 'ANG-001'],
            ['Siti Rahmawati', 'siti@perpustakaan.test', 'ANG-002'],
            ['Andi Wijaya', 'andi@perpustakaan.test', 'ANG-003'],
            ['Dewi Lestari', 'dewi@perpustakaan.test', 'ANG-004'],
            ['Rizky Pratama', 'rizky@perpustakaan.test', 'ANG-005'],
        ])->map(fn (array $m) => User::create([
            'name' => $m[0],
            'email' => $m[1],
            'password' => 'password',
            'role' => User::ROLE_ANGGOTA,
            'member_code' => $m[2],
        ]));

        // --- Kategori & buku ---
        foreach ($this->collection as $categoryName => $books) {
            $category = Category::create([
                'name' => $categoryName,
                'description' => "Koleksi kategori {$categoryName} perpustakaan.",
            ]);

            foreach ($books as [$title, $author, $publisher, $year, $isbn, $stock, $desc]) {
                Book::create([
                    'category_id' => $category->id,
                    'title' => $title,
                    'author' => $author,
                    'publisher' => $publisher,
                    'publication_year' => $year,
                    'isbn' => $isbn,
                    'stock_total' => $stock,
                    'stock_available' => $stock,
                    'description' => $desc,
                ]);
            }
        }

        // --- Riwayat peminjaman supaya dashboard hidup ---
        $bookIds = Book::pluck('id')->shuffle()->values();

        // Sedang dipinjam
        $this->createLoan($members[0]->id, $bookIds[0], 5, 9, Loan::STATUS_DIPINJAM);
        // Terlambat 6 hari
        $this->createLoan($members[1]->id, $bookIds[1], 20, -6, Loan::STATUS_DIPINJAM);
        // Kembali tepat waktu
        $loan = $this->createLoan($members[2]->id, $bookIds[2], 30, -16, Loan::STATUS_DIKEMBALIKAN);
        $loan->update(['returned_at' => today()->subDays(18), 'fine_amount' => 0]);
        // Kembali terlambat, kena denda
        $loan = $this->createLoan($members[3]->id, $bookIds[3], 40, -26, Loan::STATUS_DIKEMBALIKAN);
        $loan->update(['returned_at' => today()->subDays(20), 'fine_amount' => 6000]);
        // Peminjam berulang: 2 buku aktif (mendekati batas 3)
        foreach ([4, 5] as $i) {
            $this->createLoan($members[4]->id, $bookIds[$i], 2, 12, Loan::STATUS_DIPINJAM);
        }

        // ── Reservasi demo ──
        // Buku yang stoknya masih ada → langsung "tersedia" (siap diambil).
        $availableBook = Book::where('stock_available', '>', 0)->whereNotIn('id', $bookIds->take(6))->first();
        if ($availableBook) {
            app(\App\Services\ReservationService::class)->reserve($availableBook, $members[0]->id);
        }

        // Buku yang lagi dipinjam orang → antrean "menunggu".
        $scarceBook = Book::find($bookIds[0]);
        if ($scarceBook && $scarceBook->stock_available === 0) {
            app(\App\Services\ReservationService::class)->reserve($scarceBook, $members[2]->id);
            app(\App\Services\ReservationService::class)->reserve($scarceBook, $members[3]->id);
        }

        $this->command?->info('Seeder selesai: '.User::count().' user, '.Book::count().' buku, '.Loan::count().' riwayat pinjam, '.Reservation::count().' reservasi.');
        $this->command?->warn('Login admin: admin@perpustakaan.test / password');
    }

    private function createLoan(int $userId, int $bookId, int $borrowedDaysAgo, int $dueInDays, string $status): Loan
    {
        $loan = Loan::create([
            'user_id' => $userId,
            'book_id' => $bookId,
            'borrowed_at' => today()->subDays($borrowedDaysAgo),
            'due_at' => today()->addDays($dueInDays),
            'status' => $status,
        ]);

        Book::find($bookId)?->decrement('stock_available');

        return $loan;
    }
}
