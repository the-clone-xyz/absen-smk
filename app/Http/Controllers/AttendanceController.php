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

        // --- A. CEK APAKAH SUDAH ABSEN HARI INI? ---
        $sudahAbsen = Attendance::where('user_id', $user->id)
                        ->where('date', now()->toDateString())
                        ->first();

        // PENGECUALIAN: Jika scan QR Kelas, kita izinkan scan lagi (karena bisa jadi absen mapel beda)
        // Tapi untuk simplifikasi sistem ini: "1 Hari = 1 Absen Masuk".
        // Jadi kalau sudah absen pagi, scan kelas hanya untuk validasi (tidak nambah data baru di tabel attendance)
        // Nanti Guru akan melihat status "Hadir" dari data absen pagi tersebut.
        
        if ($sudahAbsen) {
            // Jika User Scan QR Kelas -> Kita beri pesan sukses saja (Validasi kehadiran mapel)
            if ($request->input('type') === 'qr' && str_starts_with($request->qr_code, 'CLASS:')) {
                 return redirect()->route('dashboard')
                    ->with('success', 'Anda sudah tercatat Hadir hari ini. Data sinkron ke Guru.');
            }
            
            return redirect()->route('dashboard')
                ->with('error', 'Anda sudah melakukan presensi hari ini!');
        }

        // --- B. PERSIAPAN VARIABEL ---
        $type = $request->input('type', 'face');
        $photoPath = null;
        $description = '';
        
        // --- C. LOGIKA QR CODE (KELAS & HARIAN) ---
        if ($type === 'qr') {
            $request->validate(['qr_code' => 'required']);
            $qrString = $request->qr_code;

            // 1. Cek: Apakah ini QR KELAS? (Format: CLASS:ID:TOKEN)
            if (str_starts_with($qrString, 'CLASS:')) {
                $parts = explode(':', $qrString);
                
                // Validasi format string
                if (count($parts) !== 3) {
                    return back()->withErrors(['qr' => 'Format QR Code tidak dikenali.']);
                }

                $scheduleId = $parts[1];
                $tokenHash  = $parts[2];

                // Validasi Token menggunakan Trait (Context: SCH-ID)
                if (! $this->isTokenValid($tokenHash, 'SCH-' . $scheduleId)) {
                    return back()->withErrors(['qr' => 'QR Kelas Kadaluarsa! Minta Guru refresh layar.']);
                }

                $description = 'Hadir via QR Kelas (Jadwal ID: ' . $scheduleId . ')';
            } 
            // 2. Jika Bukan, Berarti QR HARIAN (Format: TOKEN_HASH saja)
            else {
                // Validasi Token Harian (Context Default)
                if (! $this->isTokenValid($qrString)) {
                    return back()->withErrors(['qr' => 'QR Code Salah atau Kadaluarsa!']);
                }
                $description = 'Hadir via QR Harian';
            }

            // Simpan Foto (Opsional di mode QR)
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photos', 'public');
            }
        } 
        
        // --- D. LOGIKA WAJAH / GPS ---
        else {
            $request->validate([
                'latitude' => 'required',
                'longitude' => 'required',
                'photo' => 'required|image|max:10240',
            ]);

            // Ambil Setting Radius dari DB
            $setting = SystemSetting::first();
            if (!$setting) return back()->withErrors(['location' => 'Admin belum setting lokasi!']);

            $jarak = $this->hitungJarak($setting->latitude, $setting->longitude, $request->latitude, $request->longitude);

            if ($jarak > $setting->radius_limit) {
                 return redirect()->back()->withErrors(['location' => "Jarak kejauhan: " . round($jarak) . "m. (Max: {$setting->radius_limit}m)"]);
            }

            $description = 'Hadir via Wajah (GPS)';
            $photoPath = $request->file('photo')->store('photos', 'public');
        }

        // --- E. SIMPAN KE DATABASE ---
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'time_in' => now()->toTimeString(),
            'status' => 'Hadir',
            'description' => $description,
            'approval_status' => 'approved', // Hadir langsung Approved
            'photo_path' => $photoPath,
            'latitude' => $request->latitude ?? 0,
            'longitude' => $request->longitude ?? 0,
        ]);

        return redirect()->route('dashboard')->with('success', 'Absensi Berhasil Tercatat!');
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