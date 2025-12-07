<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Journal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // --- 0. REDIRECT USER (Jika salah kamar) ---
        // Pastikan Guru & Admin tidak nyasar ke dashboard siswa
        if ($user->role === 'teacher') {
            return redirect()->route('teacher.dashboard');
        }
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $userId = $user->id;
        
        // Load relasi student untuk dapat class_id
        $user->load('student'); 
        $classId = $user->student->class_id ?? null;

        // --- 1. STATISTIK ABSENSI (BULAN INI) ---
        // Hitung statistik hanya untuk bulan berjalan agar relevan
        $hadir = Attendance::where('user_id', $userId)
                    ->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->where('status', 'Hadir')->count();
                    
        $sakit = Attendance::where('user_id', $userId)
                    ->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->whereIn('status', ['Sakit', 'Izin'])->count();
                    
        $total = Attendance::where('user_id', $userId)
                    ->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->count();

        // --- 2. JADWAL PELAJARAN HARI INI ---
        Carbon::setLocale('id');
        $hariIni = Carbon::now()->isoFormat('dddd'); // Contoh: "Senin"

        $jadwalHariIni = [];

        if ($classId) {
             $jadwalHariIni = Schedule::with(['subject', 'teacher.user'])
                ->where('class_id', $classId)
                ->where('day', $hariIni)
                ->orderBy('start_time')
                ->get()
                ->map(function ($item) {
                    // Cek apakah Guru sudah bikin jurnal hari ini? (Indikator Kelas Dimulai)
                    $isStarted = Journal::where('schedule_id', $item->id)
                                    ->where('date', now()->toDateString())
                                    ->exists();

                    return [
                        'id' => $item->id,
                        'start_time' => $item->start_time, 
                        'end_time' => $item->end_time,
                        'subject' => $item->subject, // Kirim object subject lengkap
                        'teacher' => $item->teacher, // Kirim object teacher lengkap
                        'is_started' => $isStarted, 
                    ];
                });
        }

        // --- 3. DATA RIWAYAT BULANAN (UNTUK KALENDER) ---
        // Ini data penting agar Kalender Siswa ada titik hijaunya
        $riwayatAbsen = Attendance::where('user_id', $userId)
                            ->whereMonth('date', now()->month)
                            ->whereYear('date', now()->year)
                            ->get()
                            ->keyBy('date'); // Format key: "2025-12-03" agar mudah dicari di JS

        // --- KIRIM KE VUE ---
        return Inertia::render('Dashboard', [
            'statistik' => [
                'hadir' => $hadir,
                'sakit' => $sakit,
                'total' => $total
            ],
            // Gunakan nama 'jadwal' agar sesuai dengan props di Dashboard.vue
            'jadwal' => $jadwalHariIni, 
            // Kirim data riwayat absen untuk kalender
            'riwayatAbsen' => $riwayatAbsen 
        ]);
    }
}