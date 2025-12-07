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
        // Ambil pengaturan sekolah
        $settings = SystemSetting::first();

        return Inertia::render('Attendance/Index', [
            'schoolSettings' => [
                // PERBAIKAN: Sesuaikan dengan nama kolom di database Anda (image_ab59b9.jpg)
                'lat' => $settings ? (float) $settings->latitude : 0,
                'lng' => $settings ? (float) $settings->longitude : 0,
                'radius' => $settings ? (int) $settings->radius_limit : 50, // Kolomnya 'radius_limit'
            ]
        ]);
    }

    // 2. Halaman Izin
    public function izin()
    {
        return Inertia::render('Attendance/Izin');
    }

    // 3. PROSES SIMPAN ABSEN
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'type' => 'required|in:face,qr',
            'photo' => 'nullable|image|max:5120',
        ]);

        $redirectRoute = $user->role === 'teacher' ? 'teacher.dashboard' : 'dashboard';

        // A. CEK DUPLIKAT
        $sudahAbsen = Attendance::where('user_id', $user->id)
                        ->where('date', now()->toDateString())
                        ->first();

        if ($sudahAbsen) {
            return redirect()->route($redirectRoute)
                ->with('error', 'Anda sudah melakukan presensi hari ini!');
        }

        // B. VALIDASI JARAK (SERVER SIDE)
        $settings = SystemSetting::first();
        if ($settings) {
            // PERBAIKAN: Gunakan 'latitude' dan 'longitude' dari database
            $jarak = $this->hitungJarak(
                $request->latitude, 
                $request->longitude, 
                $settings->latitude, 
                $settings->longitude
            );

            // PERBAIKAN: Gunakan 'radius_limit'
            $maxRadius = (int) $settings->radius_limit;

            if ($jarak > $maxRadius) {
                return back()->withErrors(['location' => "Posisi Anda diluar radius! Jarak: " . round($jarak) . "m (Max: {$maxRadius}m)"]);
            }
        }

        // C. VALIDASI TIPE ABSEN
        $type = $request->input('type', 'face');
        $photoPath = null;
        $description = '';

        if ($type === 'qr') {
            if (! $this->isTokenValid($request->qr_code)) {
                return back()->withErrors(['qr' => 'QR Code tidak valid!']);
            }
            $description = 'Absensi via QR Code';
        } else {
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('attendance_photos', 'public'); 
                $description = 'Absensi Wajah';
            } else {
                return back()->withErrors(['photo' => 'Foto selfie wajib ada!']);
            }
        }

        // D. SIMPAN
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'time_in' => now()->toTimeString(),
            'status' => 'Hadir',
            'description' => $description,
            'approval_status' => 'approved',
            'photo_path' => $photoPath,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route($redirectRoute)->with('success', 'Absensi Berhasil Tercatat!');
    }

    // Rumus Haversine
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