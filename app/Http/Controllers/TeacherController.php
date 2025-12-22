<?php

namespace App\Http\Controllers;

use App\Models\Task; // <--- SUDAH DITAMBAHKAN
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Journal;
use App\Models\Student;
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

        Carbon::setLocale('id');
        $hariIni = Carbon::now()->isoFormat('dddd');

        // A. JADWAL HARI INI
        $jadwalHariIni = [];
        if ($teacher) {
            // PERBAIKAN: Ganti 'class' jadi 'kelas'
            $jadwalHariIni = Schedule::with(['kelas', 'subject'])
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

        // B. JADWAL KALENDER
        // PERBAIKAN: Ganti 'class' jadi 'kelas'
        $jadwalBulanan = Schedule::with(['kelas', 'subject'])
                            ->where('teacher_id', $teacher->id)
                            ->get()
                            ->groupBy('day');

        // C. ABSENSI PENDING
        // PERBAIKAN: Pastikan eager loading 'kelas' benar
        $absensiGrouped = Attendance::with(['user.student.kelas'])
                            ->whereDate('created_at', now()->toDateString())
                            ->where('approval_status', 'pending')
                            ->latest()
                            ->get()
                            ->groupBy(function($item) {
                                // PERBAIKAN: Akses properti 'kelas'
                                return $item->user->student->kelas->name ?? 'Lainnya';
                            });

        // D. STATISTIK
        $stats = [
            'hadir' => Attendance::whereDate('created_at', now()->toDateString())->where('status', 'Hadir')->count(),
            'pending' => Attendance::whereDate('created_at', now()->toDateString())->where('approval_status', 'pending')->count(),
            'total' => Attendance::whereDate('created_at', now()->toDateString())->count(),
        ];

        return Inertia::render('Teacher/Dashboard', [
            'absensiGrouped' => $absensiGrouped,
            'statistik' => $stats,
            'jadwal' => $jadwalHariIni,
            'jadwalKalender' => $jadwalBulanan
        ]);
    }

    // ==========================================
    // 2. MANAJEMEN KELAS (JURNAL & TUGAS)
    // ==========================================
    
    public function showClass($scheduleId)
    {
        $teacherId = auth()->user()->teacher->id;
        
        // 1. Ambil Jadwal & Siswa
        // PERBAIKAN: Ganti 'class' jadi 'kelas'
        $schedule = Schedule::with(['kelas.students.user', 'subject'])
                    ->where('id', $scheduleId)
                    ->where('teacher_id', $teacherId)
                    ->firstOrFail();
        
        // 2. Cek Jurnal Hari Ini
        $existingJournal = Journal::with('attendances')
                ->where('schedule_id', $scheduleId)
                ->where('date', now()->toDateString())
                ->first();

        // 3. Ambil Data Tugas
        // PERBAIKAN: Akses $schedule->kelas->id
        $tasks = Task::with('submissions.student')
                ->where('kelas_id', $schedule->kelas->id)     
                ->where('subject_id', $schedule->subject->id) 
                ->latest()
                ->get();

        return Inertia::render('Teacher/Classroom', [
            'schedule' => $schedule,
            // PERBAIKAN: Akses $schedule->kelas
            'students' => $schedule->kelas->students,
            'existingJournal' => $existingJournal,
            'tasks' => $tasks
        ]);
    }

    // SIMPAN JURNAL & ABSEN (SATU TOMBOL)
    public function storeJournal(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'topic' => 'required|string',
            'notes' => 'nullable|string',
            'attendances' => 'required|array',
            'module' => 'nullable|file|max:21240',
        ]);

        $teacher = auth()->user()->teacher;
        
        DB::transaction(function () use ($request, $teacher) {
            
            // 1. Simpan/Update Jurnal
            $journalData = [
                'schedule_id' => $request->schedule_id,
                'teacher_id' => $teacher->id,
                'date' => now()->toDateString(),
                'topic' => $request->topic,
                'notes' => $request->notes,
            ];

            if ($request->hasFile('module')) {
                $journalData['module_path'] = $request->file('module')->store('journals', 'public');
            }

            $journal = Journal::updateOrCreate(
                ['schedule_id' => $request->schedule_id, 'date' => now()->toDateString()],
                $journalData
            );

            // 2. Simpan Log Absensi (JournalHistory)
            DB::table('journal_attendances')->where('journal_id', $journal->id)->delete();
            
            $journalLogs = [];
            foreach ($request->attendances as $studentId => $status) {
                $journalLogs[] = [
                    'journal_id' => $journal->id,
                    'student_id' => $studentId,
                    'status' => $status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // 3. UPDATE ABSENSI HARIAN UTAMA
                $student = Student::find($studentId);
                if ($student) {
                    Attendance::updateOrCreate(
                        [
                            'user_id' => $student->user_id,
                            'date' => now()->toDateString()
                        ],
                        [
                            'time_in' => now()->toTimeString(),
                            'status' => $status,
                            'approval_status' => 'approved',
                            'description' => 'Absen Kelas (Jurnal)',
                            'latitude' => 0,
                            'longitude' => 0
                        ]
                    );
                }
            }
            
            DB::table('journal_attendances')->insert($journalLogs);
        });
        
        return redirect()->route('teacher.dashboard')->with('success', 'Kelas selesai! Data tersimpan.');
    }

    public function getQrToken() { return response()->json(['token' => $this->generateAttendanceToken(0)]); }

    public function getClassQrToken($scheduleId) {
        $teacherId = auth()->user()->teacher->id;
        $schedule = Schedule::where('id', $scheduleId)->where('teacher_id', $teacherId)->firstOrFail();
        $rawToken = $this->generateAttendanceToken(0, 'SCH-' . $scheduleId);
        return response()->json(['token' => "CLASS:" . $scheduleId . ":" . $rawToken]);
    }

    public function getClassData($scheduleId) {
        // PERBAIKAN: Ganti 'class' jadi 'kelas'
        $schedule = Schedule::with('kelas.students.user')->findOrFail($scheduleId);
        // PERBAIKAN: Akses $schedule->kelas
        $students = $schedule->kelas->students;
        $studentUserIds = $students->pluck('user_id');
        $dailyAttendance = Attendance::whereDate('date', now()->toDateString())->whereIn('user_id', $studentUserIds)->get()->keyBy('user_id');

        $mappedStudents = $students->map(function ($student) use ($dailyAttendance) {
            $status = 'Alpha';
            if ($dailyAttendance->has($student->user_id)) {
                $status = $dailyAttendance->get($student->user_id)->status;
            }
            return [ 'id' => $student->id, 'status' => $status ];
        });
        return response()->json(['students' => $mappedStudents]);
    }

    public function updateStatus(Request $request, $id) {
        $attendance = Attendance::findOrFail($id);
        $attendance->update([
            'approval_status' => $request->status,
            'status' => $request->status === 'rejected' ? 'Alpha' : $attendance->status
        ]);
        return back()->with('success', 'Status diperbarui!');
    }

    public function showPending() {
        $pendingRequests = Attendance::with('user')->where('approval_status', 'pending')->latest()->get();
        return Inertia::render('Teacher/Approval', ['pendingRequests' => $pendingRequests]);
    }

    public function previewFile(Request $request)
    {
        $path = $request->query('path'); 
        
        if (!$path) { abort(404); }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $forbidden = ['exe', 'bat', 'sh', 'php', 'js', 'sql'];

        if (in_array($extension, $forbidden)) {
            abort(403, 'Tipe file ini dilarang untuk dibuka demi keamanan.');
        }

        $url = asset('storage/' . $path);
        $filename = basename($path);

        return Inertia::render('Teacher/FilePreview', [
            'fileUrl' => $url,
            'fileName' => $filename,
            'extension' => $extension
        ]);
    }
}