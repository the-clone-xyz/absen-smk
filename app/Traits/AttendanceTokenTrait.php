<?php

namespace App\Traits;

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon; // Pastikan Carbon sudah terimport

trait AttendanceTokenTrait
{
    private const TIME_INTERVAL = 15; // Interval perubahan token dalam detik

    /**
     * Menghasilkan token unik berdasarkan waktu (berubah setiap 15 detik).
     */
    protected function generateAttendanceToken(int $offset = 0): string
    {
        // Ambil waktu saat ini, dikurangi offset jika diperlukan
        $now = Carbon::now()->subSeconds($offset);
        
        // Total detik sejak awal hari
        $totalSeconds = $now->secondsSinceMidnight(); 

        // Hitung Block waktu saat ini (setiap 15 detik)
        // Contoh: jam 10:00:00 = Block 0, jam 10:00:15 = Block 1, dst.
        $timeBlock = floor($totalSeconds / self::TIME_INTERVAL);

        // TimeSeed: Tahun, Bulan, Tanggal, dan Block 15 detik
        $timeSeed = $now->format('Ymd') . '-' . $timeBlock;

        // Menggunakan SHA1 agar tokennya pendek dan unik per block waktu
        return sha1($timeSeed . env('APP_KEY')); 
    }

    /**
     * Mengecek apakah token yang discan masih valid (toleransi 14 detik ke belakang).
     */
    protected function isTokenValid(string $scannedToken): bool
    {
        // Token yang valid saat ini (detik ke 0-14)
        $currentToken = $this->generateAttendanceToken(0);
        
        // Token blok sebelumnya (toleransi, token yang dihasilkan 14 detik yang lalu)
        $previousToken = $this->generateAttendanceToken(self::TIME_INTERVAL - 1); 

        // Token valid jika cocok dengan token saat ini atau token blok sebelumnya
        return $scannedToken === $currentToken || $scannedToken === $previousToken;
    }
}