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
        $settings = SystemSetting::first();

        return Inertia::render('Attendance/Index', [
            'schoolSettings' => [
                'lat' => $settings ? (float) $settings->latitude : 0,
                'lng' => $settings ? (float) $settings->longitude : 0,
                'radius' => $settings ? (int) $settings->radius_limit : 50,
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
        
        // 1. VALIDASI INPUT (Hybrid: GPS Wajib hanya untuk Selfie)
        $request->validate([
            'type'      => 'required|in:face,qr',
            'latitude'  => 'required_if:type,face', 
            'longitude' => 'required_if:type,face',
            'photo'     => 'nullable|image|max:5120',
            'qr_code'   => 'required_if:type,qr',
        ]);

        $redirectRoute = $user->role === 'teacher' ? 'teacher.dashboard' : 'student.dashboard';

        // 2. CEK DUPLIKAT (Hanya boleh 1x absen masuk per hari)
        $sudahAbsen = Attendance::where('user_id', $user->id)
                        ->where('date', now()->toDateString())
                        ->exists();

        if ($sudahAbsen) {
            return redirect()->route($redirectRoute)
                ->with('error', 'Anda sudah melakukan presensi hari ini!');
        }

        // 3. LOGIKA HYBRID (Face vs QR)
        $type = $request->input('type');
        $photoPath = null;
        $description = '';
        $settings = SystemSetting::first();
        
        // Penampung Koordinat (Bisa null jika QR)
        $lat = $request->latitude;
        $long = $request->longitude;

        if ($type === 'face') {
            // --- LOGIKA WAJAH (Wajib GPS & Foto) ---
            if (!$request->hasFile('photo')) return back()->withErrors(['photo' => 'Foto selfie wajib!']);

            if ($settings) {
                $jarak = $this->hitungJarak($lat, $long, $settings->latitude, $settings->longitude);
                $maxRadius = (int) $settings->radius_limit + 20; // Toleransi 20m
                if ($jarak > $maxRadius) return back()->withErrors(['location' => "Kejauhan! Jarak: ".round($jarak)."m."]);
            }
            
            $photoPath = $request->file('photo')->store('attendance_photos', 'public'); 
            $description = 'Absensi Wajah (GPS Valid)';

        } else {
            // --- LOGIKA QR CODE (Cerdas) ---
            $qrString = $request->qr_code;

            // CEK A: Apakah ini QR Kelas? (Format: CLASS:ID:TOKEN)
            if (str_starts_with($qrString, 'CLASS:')) {
                $parts = explode(':', $qrString);
                
                if (count($parts) === 3) {
                    $scheduleId = $parts[1]; // ID Jadwal (misal: 14)
                    $realToken = $parts[2];  // Token Asli
                    
                    // VALIDASI TOKEN KHUSUS KELAS
                    // Pastikan token valid untuk scope 'SCH-14'
                    if (! $this->isTokenValid($realToken, 'SCH-' . $scheduleId)) {
                        return back()->withErrors(['qr' => 'QR Kelas Kadaluarsa/Salah!']);
                    }

                    $description = "Hadir di Kelas (Jadwal #$scheduleId)";
                } else {
                    return back()->withErrors(['qr' => 'Format QR Kelas Rusak!']);
                }

            } else {
                // CEK B: QR Master (Admin) - Tanpa Scope
                if (! $this->isTokenValid($qrString)) {
                    return back()->withErrors(['qr' => 'QR Code Tidak Valid!']);
                }
                $description = 'Absensi via QR Master';
            }

            // Pastikan lat/long null biar tidak error di DB
            if (empty($lat) || empty($long)) {
                $lat = null; 
                $long = null;
            }
        }

        // 4. SIMPAN KE DATABASE
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'time_in' => now()->toTimeString(),
            'status' => 'Hadir',
            'description' => $description,
            'approval_status' => 'approved',
            'photo_path' => $photoPath,
            'latitude' => $lat,
            'longitude' => $long,
        ]);

        return redirect()->route($redirectRoute)->with('success', 'Presensi Berhasil!');
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