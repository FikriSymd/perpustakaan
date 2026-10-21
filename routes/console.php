<?php

use Illuminate\Support\Facades\Schedule;

// Pengingat keterlambatan + siklus reservasi (kadaluarsa & promosi antrean).
// Jalankan tiap hari pukul 07:00 — butuh cron entry:
//   * * * * * php /path/ke/project/artisan schedule:run >> /dev/null 2>&1
Schedule::command('library:notify-overdue')->dailyAt('07:00');
