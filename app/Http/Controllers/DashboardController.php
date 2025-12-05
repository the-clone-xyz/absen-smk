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
        $userId = $user->id;

        // --- 1. STATISTIK ABSENSI ---
        $hadir = Attendance::where('user_id', $userId)->where('status', 'Hadir')->count();
        $sakit = Attendance::where('user_id', $userId)->where('status', 'Sakit')->count();
        $izin  = Attendance::where('user_id', $userId)->where('status', 'Izin')->count();
        $totalIzinSakit = $sakit + $izin;

        // --- 2. JADWAL PELAJARAN HARI INI ---
        Carbon::setLocale('id');
        $hariIni = Carbon::now()->isoFormat('dddd'); // Contoh: "Senin"

        $jadwalHariIni = [];

        // Cek apakah siswa sudah punya kelas?
        if ($user->role === 'student' && $user->class_id) {
             $jadwalHariIni = Schedule::with(['subject', 'teacher.user'])
                                ->where('class_id', $user->class_id) // Filter sesuai kelas siswa
                                ->where('day', $hariIni)             // Filter hari ini
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
                                        'subject' => $item->subject->name,
                                        'teacher' => $item->teacher->user->name, // Nama Guru
                                        'is_started' => $isStarted, // Status Live
                                    ];
                                });
        }

        // --- 3. DATA GRAFIK (6 BULAN TERAKHIR) ---
        $chartLabels = [];
        $chartValues = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $chartLabels[] = $date->isoFormat('MMM'); // Jan, Feb...
            
            // Hitung jumlah hadir di bulan tersebut
            $count = Attendance::where('user_id', $userId)
                        ->where('status', 'Hadir')
                        ->whereYear('date', $date->year)
                        ->whereMonth('date', $date->month)
                        ->count();
            
            $chartValues[] = $count;
        }

        // Kirim Semua Data ke Vue
        return Inertia::render('Dashboard', [
            'statistik' => [
                'hadir' => $hadir,
                'izin' => $totalIzinSakit,
                'total' => $hadir + $totalIzinSakit
            ],
            'todaySchedule' => $jadwalHariIni, // Data Jadwal Real
            'chart' => [
                'labels' => $chartLabels,
                'data' => $chartValues
            ]
        ]);
    }
}