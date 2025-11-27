<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Hitung Data dari Database
        $hadir = Attendance::where('user_id', $userId)->where('status', 'Hadir')->count();
        $sakit = Attendance::where('user_id', $userId)->where('status', 'Sakit')->count();
        $izin  = Attendance::where('user_id', $userId)->where('status', 'Izin')->count();
        
        // Gabungkan Sakit & Izin untuk statistik sederhana
        $totalIzinSakit = $sakit + $izin;

        // Kirim data ke Tampilan (Vue)
        return Inertia::render('Dashboard', [
            'statistik' => [
                'hadir' => $hadir,
                'izin' => $totalIzinSakit,
                'total' => $hadir + $totalIzinSakit
            ]
        ]);
    }
}