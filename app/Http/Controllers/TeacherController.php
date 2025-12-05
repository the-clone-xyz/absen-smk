<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Journal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Traits\AttendanceTokenTrait;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    use AttendanceTokenTrait;

    // ==========================================
    // 1. DASHBOARD GURU
    // ==========================================
    public function index()
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            // Handle error jika data guru belum lengkap
        }

        Carbon::setLocale('id');
        $hariIni = Carbon::now()->isoFormat('dddd');

        // 1. JADWAL HARI INI (Untuk Widget Kiri)
        $jadwalHariIni = [];
        if ($teacher) {
            $jadwalHariIni = Schedule::with(['class', 'subject'])
                ->where('teacher_id', $teacher->id)
                ->where('day', $hariIni)
                ->orderBy('start_time')
                ->get()
                ->map(function ($jadwal) {
                    $jurnal = Journal::where('schedule_id', $jadwal->id)
                                ->where('date', now()->toDateString())
                                ->exists();
                    $jadwal->is_done = $jurnal;
                    return $jadwal;
                });
        }

        // 2. JADWAL BULANAN (Untuk Kalender)
        // Kita ambil semua jadwal unik guru ini (Hari & Mapel)
        $jadwalBulanan = Schedule::with(['class', 'subject'])
                            ->where('teacher_id', $teacher->id)
                            ->get()
                            ->groupBy('day'); // Kelompokkan per hari (Senin, Selasa, dst)


        // 3. ABSENSI TERAKHIR (Dikelompokkan per Kelas)
        // Ambil data absensi hari ini, urutkan berdasarkan Kelas -> Waktu
        $absensiGrouped = Attendance::with(['user.student.kelas'])
                            ->whereDate('created_at', now()->toDateString())
                            ->latest()
                            ->get()
                            ->groupBy(function($item) {
                                return $item->user->student->kelas->name ?? 'Lainnya';
                            });

        // 4. Statistik (Tetap)
        $stats = [
            'hadir' => Attendance::whereDate('created_at', now()->toDateString())->where('status', 'Hadir')->count(),
            'pending' => Attendance::whereDate('created_at', now()->toDateString())->where('approval_status', 'pending')->count(),
            'total' => Attendance::whereDate('created_at', now()->toDateString())->count(),
        ];

        $currentQrToken = $this->generateAttendanceToken(0);

        return Inertia::render('Teacher/Dashboard', [
            'absensiGrouped' => $absensiGrouped, // Data Baru (Grouped)
            'statistik' => $stats,
            'qrToken' => $currentQrToken,
            'jadwal' => $jadwalHariIni,
            'jadwalKalender' => $jadwalBulanan // Data Baru (Untuk Kalender)
        ]);
    }

    // ==========================================
    // 2. MANAJEMEN KELAS (JURNAL)
    // ==========================================
    
    // Halaman Mulai Kelas
    public function showClass($scheduleId)
    {
        $teacherId = auth()->user()->teacher->id;
        
        $schedule = Schedule::with(['class.students.user', 'subject'])
                    ->where('id', $scheduleId)
                    ->where('teacher_id', $teacherId)
                    ->firstOrFail();
        
        // Cek Jurnal apakah ada di upload
        $existingJournal = Journal::with('attendances')
                ->where('schedule_id', $scheduleId)
                ->where('date', now()->toDateString())
                ->first();

        return Inertia::render('Teacher/Classroom', [
            'schedule' => $schedule,
            'students' => $schedule->class->students,
            'existingJournal' => $existingJournal,
        ]);
    }

// Simpan Jurnal & Absensi Akhir
    public function storeJournal(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'topic' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'attendances' => 'required|array',
            'module' => 'nullable|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png',
        ]);

        $teacher = auth()->user()->teacher;
        
        DB::transaction(function () use ($request, $teacher) {
            
            // 1. Cek apakah Jurnal sudah ada hari ini? (Untuk menentukan Edit atau Baru)
            $journal = Journal::where('schedule_id', $request->schedule_id)
                        ->where('date', now()->toDateString())
                        ->first();

            // 2. Handle File Upload
            // Jika ada file baru, simpan. Jika tidak, pakai path yang lama (jika ada).
            $modulePath = $journal ? $journal->module_path : null;
            
            if ($request->hasFile('module')) {
                // Simpan file ke folder 'public/modules'
                $modulePath = $request->file('module')->store('modules', 'public');
            }

            // 3. Simpan Data Jurnal (Header)
            if ($journal) {
                // --- MODE UPDATE ---
                $journal->update([
                    'topic' => $request->topic,
                    'notes' => $request->notes,
                    'module_path' => $modulePath
                ]);

                // Hapus detail absensi lama agar tidak duplikat, nanti di-insert ulang
                DB::table('journal_attendances')->where('journal_id', $journal->id)->delete();

            } else {
                // --- MODE CREATE ---
                $journal = Journal::create([
                    'schedule_id' => $request->schedule_id,
                    'teacher_id' => $teacher->id,
                    'date' => now()->toDateString(),
                    'topic' => $request->topic,
                    'notes' => $request->notes,
                    'module_path' => $modulePath
                ]);
            }

            // 4. Simpan Detail Status Siswa (Looping)
            $insertData = [];
            foreach ($request->attendances as $studentId => $status) {
                $insertData[] = [
                    'journal_id' => $journal->id,
                    'student_id' => $studentId,
                    'status' => $status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Bulk Insert (Lebih cepat daripada insert satu per satu)
            DB::table('journal_attendances')->insert($insertData);
        });
        
        return redirect()->route('teacher.dashboard')->with('success', 'Kelas selesai! Jurnal berhasil disimpan.');
    }

    // ==========================================
    // 3. API REALTIME (QR & DATA SISWA)
    // ==========================================

    // A. API Token Global (Dashboard)
    public function getQrToken()
    {
        $token = $this->generateAttendanceToken(0); 
        return response()->json(['token' => $token]);
    }

    // B. API Token Spesifik Kelas (Classroom) - INI YANG TADI HILANG
    public function getClassQrToken($scheduleId)
    {
        $teacherId = auth()->user()->teacher->id;
        
        // Validasi Kepemilikan Jadwal
        $schedule = Schedule::where('id', $scheduleId)
                    ->where('teacher_id', $teacherId)
                    ->firstOrFail();

        // Generate Token Unik dengan Context ID Jadwal
        // Format Context: SCH-[ID]
        $rawToken = $this->generateAttendanceToken(0, 'SCH-' . $scheduleId);
        
        // Format Final untuk Client: CLASS:[ID]:[TOKEN]
        $finalToken = "CLASS:" . $scheduleId . ":" . $rawToken;

        return response()->json(['token' => $finalToken]);
    }

   // API: Ambil Data Absensi Terbaru (Untuk Realtime Polling)
    public function getClassData($scheduleId)
    {
        // 1. Ambil Data Jadwal & Siswa
        $schedule = \App\Models\Schedule::with('class.students.user')->findOrFail($scheduleId);
        $students = $schedule->class->students;
        
        // 2. Cek Absensi Harian (Siapa yang sudah absen hari ini?)
        $studentUserIds = $students->pluck('user_id');
        
        $dailyAttendance = Attendance::whereDate('date', now()->toDateString())
                             ->whereIn('user_id', $studentUserIds)
                             ->get()
                             ->keyBy('user_id');

        // 3. Mapping Status untuk Frontend
        $mappedStudents = $students->map(function ($student) use ($dailyAttendance) {
            
            // Ambil record harian
            $dailyRecord = $dailyAttendance->get($student->user_id);
            
            // Default Status: Alpha
            $status = 'Alpha';

            // Jika ada data harian, gunakan status aslinya (Hadir/Sakit/Izin)
            if ($dailyRecord) {
                $status = $dailyRecord->status;
            }

            return [
                'id' => $student->id,
                'user' => $student->user, // Data user (nama, email)
                'nis' => $student->nis,
                'status' => $status // <--- Status Realtime
            ];
        });

        return response()->json([
            'students' => $mappedStudents
        ]);
    }

    // ==========================================
    // 4. APPROVAL IZIN
    // ==========================================
    
    public function updateStatus(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $request->validate(['status' => 'required|in:approved,rejected']);
        $attendance->update(['approval_status' => $request->status]);
        return back()->with('success', 'Status diperbarui!');
    }

    public function showPending()
    {
        $pendingRequests = Attendance::with('user')
            ->where('approval_status', 'pending')
            ->latest()
            ->get();
            
        return Inertia::render('Teacher/Approval', ['pendingRequests' => $pendingRequests]);
    }
}