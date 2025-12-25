<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Journal;
use App\Models\Student;
use App\Models\Kelas; // Tambahan: Model Kelas
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Traits\AttendanceTokenTrait;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    use AttendanceTokenTrait;

    public function index()
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return redirect()->route('login');
        }

        Carbon::setLocale('id');
        $hariIni = Carbon::now()->isoFormat('dddd');
        $tanggalIni = Carbon::now()->toDateString();

        // 1. JADWAL HARI INI
        $jadwalHariIni = Schedule::with(['kelas', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('day', $hariIni)
            ->orderBy('start_time')
            ->get()
            ->map(function ($jadwal) use ($tanggalIni) {
                $jadwal->is_done = Journal::where('schedule_id', $jadwal->id)
                    ->where('date', $tanggalIni)
                    ->exists();
                return $jadwal;
            });

        // 2. JADWAL KALENDER
        $jadwalBulanan = Schedule::with(['kelas', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->get()
            ->groupBy('day');

        // 3. STATISTIK ABSENSI
        $classIds = Schedule::where('teacher_id', $teacher->id)->pluck('class_id')->unique();
        $studentUserIds = Student::whereIn('class_id', $classIds)->pluck('user_id');
        $totalSiswa = $studentUserIds->count();

        $attendanceStats = Attendance::whereIn('user_id', $studentUserIds)
            ->where('date', $tanggalIni)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $stats = [
            'total_siswa' => $totalSiswa,
            'hadir'       => $attendanceStats['Hadir'] ?? 0,
            'izin'        => $attendanceStats['Izin'] ?? 0,
            'sakit'       => $attendanceStats['Sakit'] ?? 0,
            'alpha'       => $attendanceStats['Alpha'] ?? 0,
        ];

        // 4. DATA APPROVAL (IZIN)
        $absensiGrouped = Attendance::with(['user.student.kelas'])
            ->whereIn('user_id', $studentUserIds)
            ->where('date', $tanggalIni)
            ->where('approval_status', 'pending')
            ->latest()
            ->get()
            ->groupBy(function ($item) {
                return $item->user->student->kelas->name ?? 'Lainnya';
            });

        // 5. DATA KELAS SAYA (Untuk Slider Dashboard)
        $kelasSaya = Schedule::with(['kelas.students', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->get()
            ->groupBy('class_id') // Group berdasarkan ID Kelas Fisik
            ->map(function ($schedulesInClass) {
                $firstSchedule = $schedulesInClass->first();
                return [
                    'class_name' => $firstSchedule->kelas->name, // Nama Kelas (Induk)
                    'student_count' => $firstSchedule->kelas->students->count(),
                    // Ambil mapel unik yang diajar guru ini di kelas tsb
                    'subjects' => $schedulesInClass->unique('subject_id')->map(function ($s) {
                        return [
                            'schedule_id' => $s->id, // Link ke detail mapel
                            'name' => $s->subject->name // Nama Mapel
                        ];
                    })->values()
                ];
            })->values();

        return Inertia::render('Teacher/Dashboard', [
            'auth' => ['user' => $user],
            'absensiGrouped' => $absensiGrouped,
            'statistik' => $stats,
            'jadwal' => $jadwalHariIni,
            'jadwalKalender' => $jadwalBulanan,
            'kelas' => $kelasSaya, 
        ]);
    }

    public function show($id)
    {
        $teacherId = auth()->user()->teacher->id;
        $schedule = Schedule::with(['kelas.students', 'subject'])
            ->where('id', $id)
            ->where('teacher_id', $teacherId)
            ->firstOrFail();

        $sessionCount = Journal::where('schedule_id', $schedule->id)->count();
        $currentSession = $sessionCount + 1;
        $totalSessions = 16; 
        
        $progressPercentage = ($sessionCount / $totalSessions) * 100;

        // 1. Data Lengkap Siswa (NIS, NISN, TTL, No WA)
        $students = $schedule->kelas->students->map(function ($student) {
            return [
                'id'    => $student->id,
                'name'  => $student->name,
                'nis'   => $student->nis ?? '-',
                'nisn'  => $student->nisn ?? '-',
                'pob'   => $student->pob ?? '-', // Tempat Lahir
                'dob'   => $student->dob ? \Carbon\Carbon::parse($student->dob)->translatedFormat('d F Y') : '-', // Tanggal Lahir
                'phone' => $student->phone ?? null, // No WA
            ];
        });

        // 2. Data Rekap Tugas
        $assignments = Task::where('kelas_id', $schedule->class_id)
            ->where('subject_id', $schedule->subject_id)
            ->latest()
            ->get()
            ->map(function ($task) use ($schedule) {
                return [
                    'id'        => $task->id,
                    'title'     => $task->title,
                    'deadline'  => \Carbon\Carbon::parse($task->deadline)->translatedFormat('d M Y'),
                    'submitted' => $task->submissions ? $task->submissions->count() : 0,
                    'total'     => $schedule->kelas->students->count(),
                    'status'    => $task->deadline > now() ? 'active' : 'closed',
                ];
            });

        // 3. Data Bank Materi
        $materials = Journal::where('schedule_id', $schedule->id)
            ->whereNotNull('module_path')
            ->latest()
            ->get()
            ->map(function ($journal) {
                return [
                    'id'       => $journal->id,
                    'title'    => $journal->topic,
                    'type'     => pathinfo($journal->module_path, PATHINFO_EXTENSION),
                    'date'     => \Carbon\Carbon::parse($journal->date)->translatedFormat('d M Y'),
                    'file_url' => asset('storage/' . $journal->module_path)
                ];
            });

        return Inertia::render('Teacher/ClassroomShow', [
            'classroom' => [
                'id'                  => $schedule->id,
                'name'                => $schedule->kelas->name,
                'subject'             => $schedule->subject->name,
                'description'         => "Kelas " . $schedule->kelas->name . " - " . $schedule->subject->name,
                'session_completed'   => $sessionCount,
                'session_current'     => $currentSession,
                'session_total'       => $totalSessions,
                'progress_percentage' => round($progressPercentage),
                'student_count'       => $students->count(),
            ],
            'students'    => $students,
            'assignments' => $assignments,
            'materials'   => $materials,
        ]);
    }

    public function showClass($scheduleId)
    {
        $teacherId = auth()->user()->teacher->id;
        $schedule = Schedule::with(['kelas.students.user', 'subject'])
                    ->where('id', $scheduleId)
                    ->where('teacher_id', $teacherId)
                    ->firstOrFail();
        
        $existingJournal = Journal::with('attendances')
                ->where('schedule_id', $scheduleId)
                ->where('date', now()->toDateString())
                ->first();

        $tasks = Task::with('submissions.student')
                ->where('kelas_id', $schedule->class_id)
                ->where('subject_id', $schedule->subject_id) 
                ->latest()
                ->get();

        return Inertia::render('Teacher/Classroom', [
            'schedule' => $schedule,
            'students' => $schedule->kelas->students,
            'existingJournal' => $existingJournal,
            'tasks' => $tasks
        ]);
    }

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
        
        return redirect()->route('teacher.dashboard')->with('success', 'Data tersimpan.');
    }

    public function getQrToken() { return response()->json(['token' => $this->generateAttendanceToken(0)]); }

    public function getClassQrToken($scheduleId) {
        $teacherId = auth()->user()->teacher->id;
        $schedule = Schedule::where('id', $scheduleId)->where('teacher_id', $teacherId)->firstOrFail();
        $rawToken = $this->generateAttendanceToken(0, 'SCH-' . $scheduleId);
        return response()->json(['token' => "CLASS:" . $scheduleId . ":" . $rawToken]);
    }

    public function getClassData($scheduleId) {
        $schedule = Schedule::with('kelas.students.user')->findOrFail($scheduleId);
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
            abort(403, 'File tidak aman.');
        }

        return Inertia::render('Teacher/FilePreview', [
            'fileUrl' => asset('storage/' . $path),
            'fileName' => basename($path),
            'extension' => $extension
        ]);
    }
}