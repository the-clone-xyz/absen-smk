<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Traits\AttendanceTokenTrait;

class TeacherController extends Controller
{
    use AttendanceTokenTrait;
    public function index()
    {
        $currentQrToken = $this->generateAttendanceToken(0);

         // Ambil data absensi HARI INI
        $absensiHariIni = Attendance::with('user')
                            ->whereDate('created_at', now()->toDateString())
                            ->latest()
                            ->get();

        // Statistik Sederhana
        $stats = [
            'hadir' => $absensiHariIni->where('status', 'Hadir')->count(),
            'pending' => $absensiHariIni->where('approval_status', 'pending')->count(), // Menghitung yg butuh approval
            'total' => $absensiHariIni->count(),
        ];

        return Inertia::render('Teacher/Dashboard', [
            'absensi' => $absensiHariIni,
            'statistik' => $stats,
            'qrToken' => $currentQrToken, // KIRIM TOKEN KE VIEW
        ]);
    }

    // FITUR BARU: APPROVAL
    public function updateStatus(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        
        // Validasi input (hanya boleh approved/rejected)
        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $attendance->update([
            'approval_status' => $request->status
        ]);

        return back()->with('success', 'Status berhasil diperbarui!');
    }
    public function getQrToken()
    {
        $currentQrToken = $this->generateAttendanceToken(0); 

        // Mengembalikan token dalam format JSON
        return response()->json([
            'token' => $currentQrToken
        ]);
    }

    /**
     * Menampilkan daftar absensi yang status approvalnya masih 'pending'.
     */
    public function showPending()
    {
        $pendingRequests = Attendance::with('user')
            ->where('approval_status', 'pending')
            ->latest()
            ->get();

        return Inertia::render('Teacher/Approval', [
            'pendingRequests' => $pendingRequests
        ]);
    }
}