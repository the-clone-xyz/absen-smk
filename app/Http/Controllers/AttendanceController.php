<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Traits\AttendanceTokenTrait;

class AttendanceController extends Controller
{
    use AttendanceTokenTrait;

    // 1. Halaman Absen
    public function index()
    {
        return Inertia::render('Attendance/Index');
    }

    // 2. Halaman Izin
    public function izin()
    {
        return Inertia::render('Attendance/Izin');
    }

    // 3. PROSES SIMPAN ABSEN (INTI SISTEM)
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Tentukan Redirect kemana setelah absen
        // Jika Guru -> Balik ke Panel Guru
        // Jika Siswa -> Balik ke Dashboard Siswa
        $redirectRoute = $user->role === 'teacher' ? 'teacher.dashboard' : 'dashboard';

        // 1. CEK DUPLIKAT (ANTI SPAM)
        $sudahAbsen = Attendance::where('user_id', $user->id)
                        ->where('date', now()->toDateString())
                        ->first();

        if ($sudahAbsen) {
            return redirect()->route($redirectRoute)
                ->with('error', 'Anda sudah melakukan presensi hari ini!');
        }

        // 2. LOGIKA SCAN (Wajah / QR)
        $type = $request->input('type', 'face');
        $photoPath = null;
        $description = '';

        // ... (Logika Validasi QR / GPS biarkan sama seperti sebelumnya) ...
        // Pastikan validasi QR menggunakan:
        // if (! $this->isTokenValid($request->qr_code)) { ... }

        // 3. SIMPAN KE DATABASE
        Attendance::create([
            'user_id' => $user->id, // Ini akan menyimpan ID Guru jika Guru yang login
            'date' => now()->toDateString(),
            'time_in' => now()->toTimeString(),
            'status' => 'Hadir',
            'description' => $description,
            'approval_status' => 'approved',
            'photo_path' => $photoPath,
            'latitude' => $request->latitude ?? 0,
            'longitude' => $request->longitude ?? 0,
        ]);

        return redirect()->route($redirectRoute)->with('success', 'Absensi Berhasil Tercatat!');
    }

    // Rumus Jarak
    private function hitungJarak($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    public function rekap()
    {
        $dataAbsensi = Attendance::where('user_id', Auth::id())->latest()->get();
        return Inertia::render('Attendance/Rekap', ['absensi' => $dataAbsensi]);
    }
}