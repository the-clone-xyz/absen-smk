<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\Kelas;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    // --- GURU: LIHAT & BUAT TUGAS ---
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'teacher') {
            // Guru melihat tugas yang dia buat
            $tasks = Task::with(['kelas', 'subject'])
                        ->where('teacher_id', $user->id)
                        ->latest()
                        ->get();
            
            // Data untuk dropdown form
            $kelas = Kelas::all();
            $subjects = Subject::all(); // Sesuaikan jika guru punya mapel spesifik

            return Inertia::render('Teacher/Tasks/Index', [
                'tasks' => $tasks,
                'kelas_list' => $kelas,
                'subjects' => $subjects
            ]);
        } 
        
        if ($user->role === 'student') {
            // Siswa melihat tugas di kelasnya
            $studentClassId = $user->student->kelas_id; // Asumsi ada relasi student->kelas_id
            
            $tasks = Task::with(['teacher', 'subject', 'submissions' => function($q) use ($user) {
                            $q->where('student_id', $user->id);
                        }])
                        ->where('kelas_id', $studentClassId)
                        ->latest()
                        ->get()
                        ->map(function($task) {
                            // Cek apakah siswa sudah mengumpulkan
                            $task->is_submitted = $task->submissions->isNotEmpty();
                            $task->my_score = $task->submissions->first()?->score;
                            return $task;
                        });

            return Inertia::render('Student/Tasks/Index', [
                'tasks' => $tasks
            ]);
        }
    }

    // --- GURU: SIMPAN TUGAS BARU ---
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'kelas_id' => 'required',
            'subject_id' => 'required',
            'deadline' => 'required|date',
            'file' => 'nullable|file|max:2048'
        ]);

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('tasks', 'public');
        }

        Task::create([
            'teacher_id' => Auth::id(),
            'kelas_id' => $request->kelas_id,
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'attachment' => $path
        ]);

        return back()->with('success', 'Tugas berhasil dibuat!');
    }

    // --- SISWA: KUMPULKAN TUGAS ---
    public function submit(Request $request, $taskId)
    {
        $request->validate([
            'file' => 'required|file|max:5120', // Max 5MB
        ]);

        $path = $request->file('file')->store('submissions', 'public');

        TaskSubmission::updateOrCreate(
            ['task_id' => $taskId, 'student_id' => Auth::id()],
            [
                'file_path' => $path,
                'notes' => $request->notes
            ]
        );

        return back()->with('success', 'Tugas berhasil dikirim!');
    }

    public function gradeSubmission(Request $request, $id)
    {
        $request->validate([
            'score' => 'required|numeric|min:0|max:100'
        ]);

        $submission = TaskSubmission::findOrFail($id);
        $submission->update(['score' => $request->score]);

        return back()->with('success', 'Nilai berhasil disimpan!');
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        
        $request->validate([
            'title' => 'required',
            'deadline' => 'required|date',
            'file' => 'nullable|file|max:5120'
        ]);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
        ];

        if ($request->hasFile('file')) {
            $data['attachment'] = $request->file('file')->store('tasks', 'public');
        }

        $task->update($data);

        return back()->with('success', 'Tugas berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete(); // File attachment otomatis terhapus jika Anda setup observer, atau biarkan saja
        return back()->with('success', 'Tugas dihapus!');
    }
}