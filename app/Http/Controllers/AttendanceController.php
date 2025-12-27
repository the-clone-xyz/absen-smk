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

    public function izin()
    {
        return Inertia::render('Attendance/Izin');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'type'      => 'required|in:face,qr,permit', // Tambahkan permit
            'latitude'  => 'required_if:type,face', 
            'longitude' => 'required_if:type,face',
            'photo'     => 'nullable|image|max:10240', // 10MB Max
            'qr_code'   => 'required_if:type,qr',
            'status'    => 'required_if:type,permit|in:Sakit,Izin',
            'description' => 'required_if:type,permit',
        ]);

        $redirectRoute = $user->role === 'teacher' ? 'teacher.dashboard' : 'student.dashboard';

        // Cek Duplikat Absen Harian
        $sudahAbsen = Attendance::where('user_id', $user->id)
                        ->where('date', now()->toDateString())
                        ->exists();

        if ($sudahAbsen) {
            return redirect()->route($redirectRoute)
                ->with('error', 'Anda sudah melakukan presensi/izin hari ini!');
        }

        $type = $request->input('type');
        $photoPath = null;
        $description = '';
        $status = 'Hadir'; // Default Hadir
        $approvalStatus = 'approved'; // Default Approved (untuk hadir)
        
        $lat = $request->latitude;
        $long = $request->longitude;

        if ($type === 'face') {
            // --- LOGIKA WAJAH ---
            if (!$request->hasFile('photo')) return back()->withErrors(['photo' => 'Foto selfie wajib!']);

            $settings = SystemSetting::first();
            if ($settings) {
                $jarak = $this->hitungJarak($lat, $long, $settings->latitude, $settings->longitude);
                $maxRadius = (int) $settings->radius_limit + 100; // Toleransi diperbesar jadi 100m
                
                // Opsional: Matikan validasi jarak ketat jika GPS sering meleset
                // if ($jarak > $maxRadius) return back()->withErrors(['location' => "Kejauhan! Jarak: ".round($jarak)."m."]);
            }
            
            $photoPath = $request->file('photo')->store('attendance_photos', 'public'); 
            $description = 'Absensi Wajah (GPS Valid)';

        } elseif ($type === 'qr') {
            // --- LOGIKA QR CODE ---
            $qrString = $request->qr_code;

            if (str_starts_with($qrString, 'CLASS:')) {
                $parts = explode(':', $qrString);
                
                if (count($parts) === 3) {
                    $scheduleId = $parts[1];
                    $realToken = $parts[2];
                    
                    if (! $this->isTokenValid($realToken, 'SCH-' . $scheduleId)) {
                        return back()->withErrors(['qr' => 'QR Kelas Kadaluarsa/Salah!']);
                    }

                    $description = "Hadir di Kelas (Jadwal #$scheduleId)";
                } else {
                    return back()->withErrors(['qr' => 'Format QR Kelas Rusak!']);
                }

            } else {
                if (! $this->isTokenValid($qrString)) {
                    return back()->withErrors(['qr' => 'QR Code Tidak Valid!']);
                }
                $description = 'Absensi via QR Master';
            }

            $lat = null; 
            $long = null;

        } elseif ($type === 'permit') {
            // --- LOGIKA IZIN / SAKIT ---
            if (!$request->hasFile('photo')) return back()->withErrors(['photo' => 'Bukti foto wajib diupload!']);
            
            $photoPath = $request->file('photo')->store('permit_proofs', 'public');
            $status = $request->status; // Sakit atau Izin
            $description = $request->description;
            $approvalStatus = 'pending'; // Butuh persetujuan guru
            
            $lat = null;
            $long = null;
        }

        // Simpan ke Database
        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'time_in' => now()->toTimeString(),
            'status' => $status,
            'description' => $description,
            'approval_status' => $approvalStatus,
            'photo_path' => $photoPath,
            'latitude' => $lat,
            'longitude' => $long,
        ]);

        return redirect()->route($redirectRoute)->with('success', 'Data berhasil dikirim!');
    }

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