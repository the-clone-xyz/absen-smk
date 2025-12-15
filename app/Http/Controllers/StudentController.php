<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\TaskSubmission;

class StudentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Cek data siswa
        if (!$user->student) {
            return redirect('/')->with('error', 'Data siswa tidak ditemukan.');
        }

        $userId = $user->id;
        
        // PERBAIKAN 1: Ambil ID Kelas dengan benar (class_id)
        // Kita gunakan operator ?? untuk jaga-jaga jika namanya beda
        $classId = $user->student->class_id ?? $user->student->kelas_id; 

        // 1. Statistik Absensi (Gunakan 'user_id' sesuai tabel attendances Anda)
        $stats = [
            'hadir' => Attendance::where('user_id', $userId)->where('status', 'Hadir')->count(),
            'sakit' => Attendance::where('user_id', $userId)->whereIn('status', ['Sakit', 'Izin'])->count(),
            'total' => Attendance::where('user_id', $userId)->count(),
        ];

        // 2. Jadwal Hari Ini
        // Menggunakan nama hari bahasa Indonesia (Senin, Selasa, dst)
        $today = now()->locale('id')->isoFormat('dddd'); 
        
        $jadwal = [];
        if ($classId) {
            $jadwal = Schedule::with(['subject', 'teacher.user'])
                        ->where('class_id', $classId)
                        // PERBAIKAN 2: Gunakan kolom 'day' sesuai screenshot database
                        ->where('day', $today) 
                        ->orderBy('start_time')
                        ->get();
        }

        // 3. Riwayat Absen
        $riwayat = Attendance::where('user_id', $userId)
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->date => ['status' => $item->status]];
                    });

        // PERBAIKAN 3: Pastikan me-render ke Dashboard Siswa, BUKAN Admin
        return Inertia::render('Student/Dashboard', [
            'auth' => [
                'user' => $user
            ],
            'statistik' => $stats,
            'jadwal' => $jadwal,
            'riwayatAbsen' => $riwayat
        ]);
    }

    // --- FITUR KELAS & TUGAS (Biarkan Tetap Ada) ---

    public function showClass($scheduleId)
    {
        $schedule = Schedule::with(['subject', 'teacher.user', 'kelas'])->findOrFail($scheduleId);
        
        // Ambil Tugas yang ada di kelas/mapel ini
        // Pastikan menggunakan 'class_id'
        $classId = $schedule->class_id ?? $schedule->kelas_id;

        $tasks = Task::with(['submissions' => function($q) {
            $q->where('student_id', Auth::user()->student->id);
        }])
        ->where('kelas_id', $classId) 
        ->where('subject_id', $schedule->subject_id)
        ->latest()
        ->get();

        return Inertia::render('Student/Classroom', [
            'schedule' => $schedule,
            'tasks' => $tasks
        ]);
    }

    public function showTask($id)
    {
        $task = Task::with(['teacher.user', 'subject'])->findOrFail($id);
        $submission = TaskSubmission::where('task_id', $id)
                        ->where('student_id', Auth::user()->student->id)
                        ->first();

        return Inertia::render('Student/Task/Show', [
            'task' => $task,
            'submission' => $submission
        ]);
    }

    public function submitTask(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'notes' => 'nullable|string'
        ]);

        $path = $request->file('file')->store('submissions', 'public');

        TaskSubmission::updateOrCreate(
            [
                'task_id' => $id, 
                'student_id' => Auth::user()->student->id
            ],
            [
                'file_path' => $path,
                'notes' => $request->notes,
                'submitted_at' => now(),
            ]
        );

        return back()->with('success', 'Tugas berhasil dikirim!');
    }
}