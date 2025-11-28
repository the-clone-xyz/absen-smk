<?php

namespace App\Traits;

use Carbon\Carbon;

trait AttendanceTokenTrait
{
    private const TIME_INTERVAL = 15; 

    // Update parameter: tambahkan $context (bisa ID Jadwal)
    protected function generateAttendanceToken(int $offset = 0, $context = 'global'): string
    {
        $now = Carbon::now()->subSeconds($offset);
        $totalSeconds = $now->secondsSinceMidnight(); 
        $timeBlock = floor($totalSeconds / self::TIME_INTERVAL);

        // Kuncinya disini: Kita gabungkan Context (ID Jadwal) ke dalam enkripsi
        $timeSeed = $context . '-' . $now->format('Ymd') . '-' . $timeBlock;

        return sha1($timeSeed . env('APP_KEY')); 
    }

    // Update validasi juga
    protected function isTokenValid(string $scannedToken, $context = 'global'): bool
    {
        $currentToken = $this->generateAttendanceToken(0, $context);
        $previousToken = $this->generateAttendanceToken(self::TIME_INTERVAL - 1, $context); 

        return $scannedToken === $currentToken || $scannedToken === $previousToken;
    }
}