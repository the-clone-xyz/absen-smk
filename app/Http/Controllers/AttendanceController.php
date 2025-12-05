<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Traits\AttendanceTokenTrait;

class AttendanceController extends Controller
{
    use AttendanceTokenTrait;
    // 1. Halaman Absen Masuk (Scan Wajah/QR)
    public function index()
    {
        return Inertia::render('Attendance/Index');
    }

    // 2. Halaman Form Izin / Sakit
    public function izin()
    {
        return Inertia::render('Attendance/Izin');
    }

    // 3. Proses Simpan Data (Logic Utama)
    public function store(Request $request)
    {
        
        // --- 0. CEK DUPLIKAT (ANTI SPAM) ---
        // Kita cek dulu: Apakah user ini sudah punya data di tanggal hari ini?
        $sudahAbsen = Attendance::where('user_id', Auth::id())
                        ->where('date', now()->toDateString())
                        ->first();

        if ($sudahAbsen) {
            // Jika sudah ada, tolak dan kembalikan ke dashboard dengan pesan ERROR
            return redirect()->route('dashboard')
                ->with('error', 'Anda sudah melakukan presensi hari ini!');
        }

        // --- SKENARIO 1: JIKA STATUSNYA IZIN / SAKIT ---
        if ($request->status === 'Sakit' || $request->status === 'Izin') {
            $request->validate([
                'description' => 'required',
                'photo' => 'required|image|max:10240', // 10MB
            ]);

            // Simpan Foto Bukti
            $path = $request->file('photo')->store('photos', 'public');

            // Simpan Data
            Attendance::create([
                'user_id' => Auth::id(),
                'date' => now()->toDateString(),
                'time_in' => now()->toTimeString(),
                'status' => $request->status,
                'description' => $request->description,
                'approval_status' => 'pending',
                'photo_path' => $path,
                'latitude' => 0,
                'longitude' => 0,
            ]);

            // FIX: Ganti 'message' jadi 'success' agar SweetAlert muncul
            return redirect()->route('dashboard')->with('success', 'Pengajuan Izin Berhasil Dikirim!');
        }


        // --- SKENARIO 2 & 3: JIKA STATUSNYA HADIR (SCAN WAJAH / QR) ---
       $type = $request->input('type', 'face');
        $photoPath = null;
        $description = '';

        if ($type === 'qr') {
            // === LOGIKA SCAN QR CODE ===
            $request->validate(['qr_code' => 'required']);

            // Validasi Token QR
            $validToken = "SMK-TAMAN-SISWA-2025"; 

            if (! $this->isTokenValid($request->qr_code)) { // Manggil fungsi dari Trait
                return redirect()->back()->withErrors(['qr' => 'QR Code Salah atau Kadaluarsa! Silakan minta Guru untuk Refresh.']);
            }

            $description = 'Hadir via Scan QR';
            
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photos', 'public');
            }

        } else {
            // === LOGIKA SCAN WAJAH (FACE + GPS) ===
            $request->validate([
                'latitude' => 'required',
                'longitude' => 'required',
                'photo' => 'required|image|max:10240', // 10MB
            ]);

            // Koordinat Sekolah
            $setting = \App\Models\SystemSetting::first();

            if (!$setting) {
                return back()->withErrors(['location' => 'Pengaturan Lokasi Sekolah belum diatur oleh Admin!']);
            }

            $latSekolah = $setting->latitude;
            $longSekolah = $setting->longitude;
            $jarakMaksimal = $setting->radius_limit;

            $jarak = $this->hitungJarak($latSekolah, $longSekolah, $request->latitude, $request->longitude);

            if ($jarak > $jarakMaksimal) {
                 return redirect()->back()->withErrors(['location' => "Anda di luar jangkauan! Jarak: " . round($jarak) . "m. Maksimal: {$jarakMaksimal}m."]);
            }

            $description = 'Hadir via Wajah (GPS)';
            $photoPath = $request->file('photo')->store('photos', 'public');
        }

        // Simpan Data Hadir
        Attendance::create([
            'user_id' => Auth::id(),
            'date' => now()->toDateString(),
            'time_in' => now()->toTimeString(),
            'status' => 'Hadir',
            'description' => $description,
            'approval_status' => 'approved',
            'photo_path' => $photoPath,
            'latitude' => $request->latitude ?? 0,
            'longitude' => $request->longitude ?? 0,
        ]);

        // FIX: Ganti 'message' jadi 'success' agar SweetAlert muncul
        return redirect()->route('dashboard')->with('success', 'Absensi Berhasil Tercatat!');
    }

    // Fungsi Matematika Menghitung Jarak (Meter)
    private function hitungJarak($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    // Halaman Rekap/Riwayat
    public function rekap()
    {
     // PERBAIKAN: Tambahkan 'where user_id'
        $dataAbsensi = Attendance::where('user_id', Auth::id()) // <--- KUNCI FILTTERNYA
                        ->latest() // Urutkan dari yang terbaru
                        ->get();

        return Inertia::render('Attendance/Rekap', [
            'absensi' => $dataAbsensi
        ]);
    }
}