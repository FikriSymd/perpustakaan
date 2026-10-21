<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            // Kapan pengingat keterlambatan terakhir dikirim ke anggota.
            $table->timestamp('overdue_notified_at')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('overdue_notified_at');
        });
    }
};
