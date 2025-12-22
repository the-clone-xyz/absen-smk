<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\Student;
use App\Models\Kelas;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TaskController extends Controller
{
    // --- 1. HALAMAN LIST TUGAS ---
    public function index()
    {
        $user = Auth::user();

        // AMBIL DATA UNTUK DROPDOWN
        $kelas_list = Kelas::all(); 
        $subjects = Subject::all();

        // Jika Guru
        if ($user->role === 'teacher') {
            if (!$user->teacher) {
                return back()->with('error', 'Akun Anda tidak terhubung dengan data Guru.');
            }

            $tasks = Task::with(['kelas', 'subject'])
                ->where('teacher_id', $user->teacher->id)
                ->latest()
                ->get();
            
            return Inertia::render('Teacher/Tasks/Index', [
                'tasks'      => $tasks,
                'kelas_list' => $kelas_list,
                'subjects'   => $subjects
            ]);
        } 
        
        // Jika Siswa
        if ($user->role === 'student') {
            $student = $user->student;
            
            if(!$student) {
                return back()->with('error', 'Data siswa belum terhubung.');
            }

            $tasks = Task::with(['teacher.user', 'subject', 'submissions' => function($q) use ($student) {
                    $q->where('student_id', $student->id);
                }])
                ->where('kelas_id', $student->kelas_id)
                ->latest()
                ->get();

            return Inertia::render('Student/Tasks/Index', ['tasks' => $tasks]);
        }
    }

    // --- 2. GURU: SIMPAN TUGAS BARU ---
    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'kelas_id' => 'required',
            'deadline' => 'required', 
            'file'     => 'nullable|file|max:5120',
        ]);

        try {
            $cleanDeadline = str_replace('.', ':', $request->deadline);
            $formattedDeadline = Carbon::parse($cleanDeadline)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            $formattedDeadline = Carbon::tomorrow()->endOfDay()->format('Y-m-d H:i:s');
        }

        $subjectId = $request->subject_id;
        if (empty($subjectId)) {
            $firstSubject = Subject::first(); 
            $subjectId = $firstSubject ? $firstSubject->id : 1; 
        }

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('tasks', 'public');
        }

        $teacherId = Auth::user()->teacher->id; 

        Task::create([
            'teacher_id'  => $teacherId,
            'kelas_id'    => $request->kelas_id,
            'subject_id'  => $subjectId,
            'title'       => $request->title,
            'description' => $request->description ?? '-',
            'deadline'    => $formattedDeadline,
            'file'        => $path 
        ]);

        return back()->with('success', 'Tugas berhasil dibuat!');
    }

    // --- 3. GURU: LIHAT DETAIL ---
    public function show($id)
    {
        $task = Task::with(['submissions.student.user', 'kelas', 'subject'])->findOrFail($id);
        
        $students = Student::with('user')
                    ->where('class_id', $task->kelas_id) 
                    ->orderBy('nis')
                    ->get()
                    ->map(function($student) use ($task) {
                        $submission = $task->submissions->where('student_id', $student->id)->first();
                        
                        return [
                            'id' => $student->id,
                            'name' => $student->user->name,
                            'nis' => $student->nis,
                            'avatar' => $student->user->avatar,
                            'submission' => $submission
                        ];
                    });

        return Inertia::render('Teacher/Task/Show', [
            'task' => $task,
            'studentData' => $students
        ]);
    }

    // --- 4. GURU: UPDATE TUGAS ---
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        
        $request->validate([
            'title' => 'required',
            'deadline' => 'required',
            'file' => 'nullable|file|max:5120'
        ]);

        try {
            $cleanDeadline = str_replace('.', ':', $request->deadline);
            $formattedDeadline = Carbon::parse($cleanDeadline)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return back()->withErrors(['deadline' => 'Format tanggal error.']);
        }

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $formattedDeadline,
        ];

        if ($request->hasFile('file')) {
            // --- PERBAIKAN DI SINI (Ganti file_path jadi file) ---
            if ($task->file) {
                Storage::disk('public')->delete($task->file);
            }
            $data['file'] = $request->file('file')->store('tasks', 'public');
        }

        $task->update($data);

        return back()->with('success', 'Tugas berhasil diperbarui!');
    }

    // --- 5. HAPUS TUGAS ---
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        
        // --- PERBAIKAN DI SINI (Ganti file_path jadi file) ---
        if ($task->file) {
            Storage::disk('public')->delete($task->file);
        }

        // Note: Untuk submissions, kita asumsikan tabelnya memang pakai 'file_path'
        // atau Anda bisa cek tabel task_submissions nanti.
        foreach($task->submissions as $submission) {
            if($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }
        }

        $task->delete();
        
        return redirect()->back()->with('success', 'Tugas berhasil dihapus!');
    }

    // --- 6. BERI NILAI ---
    public function gradeSubmission(Request $request, $id)
    {
        $request->validate([
            'score' => 'required|numeric|min:0|max:100'
        ]);

        $submission = TaskSubmission::findOrFail($id);
        $submission->update(['score' => $request->score]);

        return back()->with('success', 'Nilai berhasil disimpan!');
    }

    // --- 7. SISWA SUBMIT ---
    public function submit(Request $request, $taskId)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'notes' => 'nullable|string'
        ]);

        $student = Auth::user()->student;

        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $path = $request->file('file')->store('submissions', 'public');

        $submission = TaskSubmission::updateOrCreate(
            [
                'task_id' => $taskId,
                'student_id' => $student->id
            ],
            [
                'file_path' => $path, // Asumsi tabel submission masih pakai file_path
                'notes' => $request->notes,
                'submitted_at' => now(),
            ]
        );

        return back()->with('success', 'Tugas berhasil dikirim!');
    }
}