<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Journal;
use App\Models\Student;
use Illuminate\Support\Facades\DB; 
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Traits\AttendanceTokenTrait;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    use AttendanceTokenTrait;

    public function index()
    {
        $user = Auth::user();
        $teacher = $user->teacher;
        if (!$teacher) return redirect()->route('login');

        Carbon::setLocale('id');
        $hariIni = Carbon::now()->isoFormat('dddd');
        $tanggalIni = Carbon::now()->toDateString();

        $jadwalHariIni = Schedule::with(['kelas', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('day', $hariIni)
            ->orderBy('start_time')
            ->get()
            ->map(function ($jadwal) use ($tanggalIni) {
                $jadwal->is_done = Journal::where('schedule_id', $jadwal->id)->where('date', $tanggalIni)->exists();
                return $jadwal;
            });

        $jadwalBulanan = Schedule::with(['kelas', 'subject'])->where('teacher_id', $teacher->id)->get()->groupBy('day');
        
        $classIds = Schedule::where('teacher_id', $teacher->id)->pluck('class_id')->unique();
        $studentUserIds = Student::whereIn('class_id', $classIds)->pluck('user_id');
        
        $attendanceStats = Attendance::whereIn('user_id', $studentUserIds)
            ->where('date', $tanggalIni)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status')->toArray();

        return Inertia::render('Teacher/Dashboard', [
            'auth' => ['user' => $user],
            'jadwal' => $jadwalHariIni,
            'jadwalKalender' => $jadwalBulanan,
            'statistik' => [
                'total_siswa' => $studentUserIds->count(),
                'hadir' => $attendanceStats['Hadir'] ?? 0,
                'izin' => $attendanceStats['Izin'] ?? 0,
                'sakit' => $attendanceStats['Sakit'] ?? 0,
                'alpha' => $attendanceStats['Alpha'] ?? 0,
            ],
            'kelas' => Schedule::with(['kelas.students', 'subject'])->where('teacher_id', $teacher->id)->get()->groupBy('class_id')->map(function ($s) {
                return [
                    'id' => $s->first()->id, 
                    'class_name' => $s->first()->kelas->name,
                    'student_count' => $s->first()->kelas->students->count(),
                    'subjects' => $s->unique('subject_id')->map(fn($x) => ['schedule_id'=>$x->id, 'name'=>$x->subject->name])->values()
                ];
            })->values()
        ]);
    }

    // --- HALAMAN KELAS INDUK & INPUT NILAI (PERBAIKAN DATA DENGAN DB BARU) ---
    public function show($id)
    {
        $teacherId = auth()->user()->teacher->id;
        $ref = Schedule::with(['kelas', 'subject'])->where('id', $id)->firstOrFail();

        // 1. Ambil KKM (KKTP) dari Database secara Dinamis
        $kkmData = DB::table('passing_grades')
            ->where('subject_id', $ref->subject_id)
            ->where('class_level', $ref->kelas->level ?? 'X') 
            ->first();
        
        $kkm = $kkmData ? $kkmData->min_score : 75; 

        // 2. Statistik
        $allRelatedSchedules = Schedule::where('class_id', $ref->class_id)
            ->where('subject_id', $ref->subject_id)->pluck('id');
        $sessionCount = Journal::whereIn('schedule_id', $allRelatedSchedules)->count();
        $totalSessions = 16; 

        // 3. Ambil Nilai Existing
        $existingGrades = DB::table('grades')
            ->where('class_id', $ref->class_id)
            ->where('subject_id', $ref->subject_id)
            ->get()->keyBy('student_id');

        // 4. Mapping Data Siswa + Nilai Rapot + DATA BARU (TP & Deskripsi)
        $students = $ref->kelas->students->load('user')->map(function ($student) use ($existingGrades) {
            $grade = $existingGrades->get($student->id);
            return [
                'id'     => $student->id,
                'name'   => $student->user->name ?? 'Siswa', 
                'avatar' => $student->user->avatar, 
                'nis'    => $student->nis ?? '-',
                'nisn'   => $student->nisn ?? '-',
                'pob'    => $student->pob,
                'dob'    => $student->dob ? \Carbon\Carbon::parse($student->dob)->translatedFormat('d F Y') : '-',
                'phone'  => $student->phone,
                
                // Data Nilai & Kurikulum Merdeka (PENTING AGAR TIDAK HILANG SAAT REFRESH)
                'grade' => [
                    'final_score'    => $grade ? $grade->final_score : 0,
                    'learning_goals' => $grade ? $grade->learning_goals : null, // JSON String TP
                    'material_scope' => $grade ? $grade->material_scope : null, // Text Lingkup Materi
                    'tp_assessment'  => $grade ? $grade->tp_assessment : null,  // JSON String Checkbox (PENTING)
                    'description'    => $grade ? $grade->description : null,    // Deskripsi Jadi
                    'predikat'       => $grade ? $grade->grade : '-',
                ],
                // Backward compatibility (jika view masih pakai field langsung)
                'final_score' => $grade ? $grade->final_score : 0,
            ];
        });

        $assignments = Task::where('kelas_id', $ref->class_id)->where('subject_id', $ref->subject_id)->latest()->get()->map(function ($t) use ($students) {
            return [
                'id' => $t->id, 'title' => $t->title, 'deadline' => \Carbon\Carbon::parse($t->deadline)->translatedFormat('d M Y'),
                'submitted' => $t->submissions->count(), 'total' => $students->count(), 'status' => $t->deadline > now() ? 'Aktif' : 'Selesai'
            ];
        });

        $materials = Journal::whereIn('schedule_id', $allRelatedSchedules)->whereNotNull('module_path')->latest()->get()->map(function ($j) {
            return [
                'id' => $j->id, 'title' => $j->topic, 'type' => pathinfo($j->module_path, PATHINFO_EXTENSION),
                'date' => \Carbon\Carbon::parse($j->date)->translatedFormat('d M Y'),
                'file_path' => $j->module_path, 'file_url' => asset('storage/' . $j->module_path)
            ];
        });

        return Inertia::render('Teacher/ClassroomShow', [
            'classroom' => [
                'id' => $ref->id, 'class_id' => $ref->class_id, 'subject_id' => $ref->subject_id,
                'name' => $ref->kelas->name, 'subject' => $ref->subject->name,
                'description' => "Kelas " . $ref->kelas->name . " - " . $ref->subject->name,
                'session_completed' => $sessionCount, 'progress_percentage' => round(($sessionCount/$totalSessions)*100),
                'kkm' => $kkm, 
            ],
            'students' => $students,
            'assignments' => $assignments,
            'materials' => $materials,
        ]);
    }

    // --- SIMPAN NILAI (PERBAIKAN UNTUK MENYIMPAN TP & DESKRIPSI) ---
    public function storeGrades(Request $request)
    {
        $request->validate([
            'class_id'   => 'required',
            'subject_id' => 'required',
            'kkm'        => 'required|numeric',
            'grades'     => 'required|array',
            'grades.*.student_id' => 'required',
            'grades.*.final_score' => 'required|numeric|min:0|max:100',
            // Validasi kolom baru
            'grades.*.learning_goals' => 'nullable', 
            'grades.*.material_scope' => 'nullable',
            'grades.*.tp_assessment'  => 'nullable', // Array boolean dari Vue
            'grades.*.description'    => 'nullable', // String deskripsi otomatis
        ]);

        $teacherId = auth()->user()->teacher->id;
        $kkm = $request->kkm;

        DB::transaction(function () use ($request, $teacherId, $kkm) {
            foreach ($request->grades as $gradeData) {
                // Logika Predikat (Interval 3 Range)
                $interval = (100 - $kkm) / 3;
                $score = $gradeData['final_score'];
                
                $predikat = 'D'; 
                if ($score >= (100 - $interval)) $predikat = 'A'; 
                elseif ($score >= (100 - ($interval * 2))) $predikat = 'B'; 
                elseif ($score >= $kkm) $predikat = 'C'; 

                // Proses TP Assessment (Array -> JSON)
                $tpAssessment = isset($gradeData['tp_assessment']) 
                    ? json_encode($gradeData['tp_assessment']) 
                    : null;

                DB::table('grades')->updateOrInsert(
                    [
                        'student_id' => $gradeData['student_id'],
                        'class_id'   => $request->class_id,
                        'subject_id' => $request->subject_id,
                        'teacher_id' => $teacherId,
                    ],
                    [
                        'semester'      => 'Ganjil', 
                        'academic_year' => '2025/2026',
                        'final_score'   => $score,
                        'knowledge_score' => $score,
                        'skill_score'     => $score,
                        'grade'           => $predikat,
                        
                        // SIMPAN KOLOM BARU (Agar data tidak hilang saat refresh)
                        'learning_goals'        => $gradeData['learning_goals'] ?? null,
                        'material_scope'        => $gradeData['material_scope'] ?? null,
                        'tp_assessment'         => $tpAssessment,
                        'description'           => $gradeData['description'] ?? null, 
                        'generated_description' => $gradeData['description'] ?? null, // Simpan juga di kolom khusus jika ada
                        
                        'updated_at' => now(),
                    ]
                );
            }
        });

        return back()->with('success', 'Nilai Rapot & Data Matriks berhasil disimpan!');
    }

    // --- FUNGSI LAINNYA TETAP SAMA ---
    public function showClass($scheduleId) {
        $teacherId = auth()->user()->teacher->id;
        $schedule = Schedule::with(['kelas.students.user', 'subject'])->where('id', $scheduleId)->where('teacher_id', $teacherId)->firstOrFail();
        $existingJournal = Journal::with('attendances')->where('schedule_id', $scheduleId)->where('date', now()->toDateString())->first();
        $tasks = Task::with('submissions.student')->where('kelas_id', $schedule->class_id)->where('subject_id', $schedule->subject_id)->latest()->get();
        return Inertia::render('Teacher/Classroom', ['schedule' => $schedule, 'students' => $schedule->kelas->students, 'existingJournal' => $existingJournal, 'tasks' => $tasks]);
    }

    public function storeJournal(Request $request) { 
        $request->validate(['schedule_id'=>'required','topic'=>'required','attendances'=>'required']);
        $teacher = auth()->user()->teacher;
        DB::transaction(function() use ($request, $teacher) {
            $data = ['schedule_id'=>$request->schedule_id, 'teacher_id'=>$teacher->id, 'date'=>now()->toDateString(), 'topic'=>$request->topic, 'notes'=>$request->notes];
            if ($request->hasFile('module')) $data['module_path'] = $request->file('module')->store('journals', 'public');
            $journal = Journal::updateOrCreate(['schedule_id'=>$request->schedule_id, 'date'=>now()->toDateString()], $data);
            DB::table('journal_attendances')->where('journal_id', $journal->id)->delete();
            $logs=[];
            foreach($request->attendances as $sid=>$stat) {
                $logs[]=['journal_id'=>$journal->id,'student_id'=>$sid,'status'=>$stat,'created_at'=>now(),'updated_at'=>now()];
                $s=Student::find($sid);
                if($s) Attendance::updateOrCreate(['user_id'=>$s->user_id,'date'=>now()->toDateString()],['time_in'=>now()->toTimeString(),'status'=>$stat,'approval_status'=>'approved','description'=>'Absen Kelas (Jurnal)','latitude'=>0,'longitude'=>0]);
            }
            DB::table('journal_attendances')->insert($logs);
        });
        return redirect()->route('teacher.dashboard')->with('success','Sesi selesai!');
    }

    public function getQrToken() { return response()->json(['token' => $this->generateAttendanceToken(0)]); }
    public function getClassQrToken($scheduleId) { $this->generateAttendanceToken(0, 'SCH-'.$scheduleId); return response()->json(['token' => "CLASS:$scheduleId:".uniqid()]); }
    public function getClassData($scheduleId) { return response()->json(['students'=>[]]); }
    public function updateStatus(Request $request, $id) { Attendance::findOrFail($id)->update(['approval_status'=>$request->status, 'status'=>$request->status==='rejected'?'Alpha':Attendance::findOrFail($id)->status]); return back(); }
    public function showPending() { return Inertia::render('Teacher/Approval', ['pendingRequests' => Attendance::with('user')->where('approval_status','pending')->get()]); }
    public function previewFile(Request $request) { return Inertia::render('Teacher/FilePreview', ['fileUrl'=>asset('storage/'.$request->query('path')), 'fileName'=>basename($request->query('path')), 'extension'=>pathinfo($request->query('path'), PATHINFO_EXTENSION)]); }
}