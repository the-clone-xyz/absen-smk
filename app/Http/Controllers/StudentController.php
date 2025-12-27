<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\Journal; 
use Carbon\Carbon;
use App\Models\SystemSetting;
use App\Models\Student; // PENTING: Tambahkan Model Student
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class StudentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->student) {
            return redirect('/')->with('error', 'Data siswa tidak ditemukan.');
        }

        $userId = $user->id;
        $classId = $user->student->class_id ?? $user->student->kelas_id; 

        $stats = [
            'hadir' => Attendance::where('user_id', $userId)->where('status', 'Hadir')->count(),
            'sakit' => Attendance::where('user_id', $userId)->whereIn('status', ['Sakit', 'Izin'])->count(),
            'total' => Attendance::where('user_id', $userId)->count(),
        ];

        Carbon::setLocale('id');
        $today = Carbon::now()->isoFormat('dddd'); 
        
        $jadwal = [];
        if ($classId) {
            $jadwal = Schedule::with(['subject', 'teacher.user'])
                        ->where('class_id', $classId)
                        ->where('day', $today) 
                        ->orderBy('start_time')
                        ->get();
        }

        $riwayat = Attendance::where('user_id', $userId)
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->date => ['status' => $item->status]];
                    });

        $todayAttendance = Attendance::where('user_id', $userId)
                            ->where('date', now()->toDateString())
                            ->first();
        $statusHariIni = $todayAttendance ? $todayAttendance->status : 'Belum Absen';

        return Inertia::render('Student/Dashboard', [
            'auth' => ['user' => $user],
            'statistik' => $stats,
            'jadwal' => $jadwal,
            'riwayatAbsen' => $riwayat,
            'attendanceStatus' => $statusHariIni
        ]);
    }

    public function showClass($scheduleId)
    {
        $schedule = Schedule::with(['subject', 'teacher.user', 'kelas'])->findOrFail($scheduleId);
        $classId = $schedule->kelas->id;

        $tasks = Task::with(['submissions' => function($q) {
            $q->where('student_id', Auth::user()->student->id);
        }])
        ->where('kelas_id', $classId)
        ->where('subject_id', $schedule->subject_id)
        ->latest()
        ->get()
        ->map(function($task) {
            $task->my_submission = $task->submissions->first();
            return $task;
        });

        $journal = Journal::where('schedule_id', $scheduleId)
                    ->where('date', now()->toDateString())
                    ->latest()
                    ->first();

        return Inertia::render('Student/Classroom', [
            'schedule' => $schedule,
            'tasks' => $tasks,
            'journal' => $journal,
            'student' => Auth::user()->student
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
        $task = Task::findOrFail($id);
        
        if ($task->deadline && now() > $task->deadline) {
            return back()->withErrors(['error' => 'Maaf, batas waktu pengumpulan tugas ini sudah berakhir.']);
        }

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
            ]
        );

        return back()->with('success', 'Tugas berhasil dikirim!');
    }


// --- FITUR CETAK KARTU (VERSI FINAL & AMAN) ---
public function card()
{
    $user = Auth::user();

    // AMBIL DATA MANUAL (Anti-Gagal)
    $student = DB::table('students')
        ->leftJoin('kelas', 'students.class_id', '=', 'kelas.id')
        ->where('students.user_id', $user->id)
        ->select('students.*', 'kelas.name as nama_kelas')
        ->first();

        $setting = DB::table('system_settings')->first();

    // FORMAT DATA UNTUK VUE
    $data = [
        'nama'      => $user->name,
        'nisn'      => $student->nisn ?? '-',
        'nis'       => $student->nis ?? '-',
        'kelas'     => $student->nama_kelas ?? 'UMUM',
        'ttl'       => ($student && $student->pob && $student->dob) ? $student->pob . ', ' . $student->dob : '-',
        'alamat'    => $student->address ?? '-',
        'foto'      => $user->avatar ? '/storage/' . $user->avatar : null,
        
        // Data Sekolah Hardcode Dulu Biar Muncul
        'sekolah' => $setting->school_name ?? 'SMK TAMANSISWA',
        'kepsek'    => 'Drs. Budi Santoso, M.Pd',
        'nip_kepsek'=> 'NIP. 19750101 200012 1 001',
        'alamat_sekolah' => 'Jl. Pendidikan No. 123',
        'website'   => 'www.smk.sch.id',
        'phone'     => '021-123456'
    ];

    return Inertia::render('Student/Card', [
    'card_data' => $data
    ]);

}
}