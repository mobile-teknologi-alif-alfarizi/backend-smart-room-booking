<?php

use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $targetStart = Carbon::now()->addMinutes(30)->startOfMinute();
    $targetEnd = Carbon::now()->addMinutes(30)->endOfMinute();

    $bookings = Booking::with(['ruangan'])
        ->whereDate('tanggal', $targetStart->toDateString())
        ->where('tipe_booking', 'jadwal_kelas')
        ->where('status', 'approved')
        ->whereBetween('waktu_mulai', [$targetStart->format('H:i:s'), $targetEnd->format('H:i:s')])
        ->get();

    foreach ($bookings as $booking) {
        $alreadySent = Notification::where('booking_id', $booking->id)
            ->where('jenis', 'kelas_reminder')
            ->where('sumber', 'system')
            ->exists();

        if ($alreadySent) {
            continue;
        }

        Notification::create([
            'user_id' => $booking->user_id,
            'booking_id' => $booking->id,
            'judul' => 'Pengingat kelas 30 menit lagi',
            'pesan' => 'Kelas Anda di ruangan ' . ($booking->ruangan->nama_ruangan ?? '-') . ' dimulai pada pukul ' . Carbon::parse($booking->waktu_mulai)->format('H:i') . '.',
            'keterangan' => 'Notifikasi pengingat kelas otomatis sistem',
            'jenis' => 'kelas_reminder',
            'sumber' => 'system',
            'dikirim_pada' => now(),
        ]);
    }
})->name('notifications:class-reminder')->everyMinute();
