<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\Student; // <--- PENTING: Import Model Student
use App\Models\Kelas;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    // --- 1. HALAMAN LIST TUGAS (Opsional jika pakai Classroom View) ---
    public function index()
    {
        $user = Auth::user();

        // Jika Guru
        if ($user->role === 'teacher') {
            $tasks = Task::with(['kelas', 'subject'])
                ->where('teacher_id', $user->teacher->id) // Pastikan relasi user->teacher ada
                ->latest()
                ->get();
            
            return Inertia::render('Teacher/Tasks/Index', ['tasks' => $tasks]);
        } 
        
        // Jika Siswa
        if ($user->role === 'student') {
            $student = $user->student;
            $tasks = Task::with(['teacher.user', 'subject', 'submissions' => function($q) use ($student) {
                    $q->where('student_id', $student->id);
                }])
                ->where('kelas_id', $student->kelas_id) // Filter sesuai kelas siswa
                ->latest()
                ->get();

            return Inertia::render('Student/Tasks/Index', ['tasks' => $tasks]);
        }
    }

    // --- 2. GURU: SIMPAN TUGAS BARU ---
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelas,id', // Validasi ID Kelas
            'subject_id' => 'required|exists:subjects,id',
            'deadline' => 'required|date',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:5120', // Max 5MB
        ]);

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('tasks', 'public');
        }

        // Ambil ID Guru dari User yang login
        $teacherId = Auth::user()->teacher->id; 

        Task::create([
            'teacher_id' => $teacherId,
            'kelas_id' => $request->kelas_id,
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'file_path' => $path // Sesuaikan nama kolom di DB (file_path / attachment)
        ]);

        return back()->with('success', 'Tugas berhasil dibuat!');
    }

    // --- 3. GURU: LIHAT DETAIL & NILAI (METHOD YANG SEBELUMNYA HILANG) ---
    public function show($id)
    {
        // Ambil data tugas beserta submisi siswa
        $task = Task::with(['submissions.student.user', 'kelas', 'subject'])->findOrFail($id);
        
        // Ambil daftar semua siswa di kelas tersebut (untuk melihat siapa yang belum kumpul)
        // Pastikan kolom di tabel students adalah 'class_id' atau 'kelas_id' sesuai database Anda
        $students = Student::with('user')
                    ->where('class_id', $task->kelas_id) 
                    ->orderBy('nis')
                    ->get()
                    ->map(function($student) use ($task) {
                        // Cari apakah siswa ini punya submission di tugas ini
                        $submission = $task->submissions->where('student_id', $student->id)->first();
                        
                        return [
                            'id' => $student->id,
                            'name' => $student->user->name,
                            'nis' => $student->nis,
                            'avatar' => $student->user->avatar, // Jika ada foto
                            'submission' => $submission // Object submission atau null
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
            'deadline' => 'required|date',
            'file' => 'nullable|file|max:5120'
        ]);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
        ];

        // Jika ada file baru, hapus file lama dan upload yang baru
        if ($request->hasFile('file')) {
            if ($task->file_path) {
                Storage::disk('public')->delete($task->file_path);
            }
            $data['file_path'] = $request->file('file')->store('tasks', 'public');
        }

        $task->update($data);

        return back()->with('success', 'Tugas berhasil diperbarui!');
    }

    // --- 5. GURU: HAPUS TUGAS ---
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        
        // Hapus file fisik jika ada
        if ($task->file_path) {
            Storage::disk('public')->delete($task->file_path);
        }

        // Hapus file submission siswa juga (Opsional, biar bersih)
        foreach($task->submissions as $submission) {
            if($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }
        }

        $task->delete();
        
        return redirect()->back()->with('success', 'Tugas berhasil dihapus!');
    }

    // --- 6. GURU: BERI NILAI ---
    public function gradeSubmission(Request $request, $id)
    {
        $request->validate([
            'score' => 'required|numeric|min:0|max:100'
        ]);

        $submission = TaskSubmission::findOrFail($id);
        $submission->update(['score' => $request->score]);

        return back()->with('success', 'Nilai berhasil disimpan!');
    }

    // --- 7. SISWA: KUMPULKAN TUGAS ---
    public function submit(Request $request, $taskId)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
            'notes' => 'nullable|string'
        ]);

        // Ambil Data Siswa dari User yang login
        $student = Auth::user()->student;

        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $path = $request->file('file')->store('submissions', 'public');

        // Cek apakah sudah pernah kumpul (untuk update) atau buat baru
        // Kita pakai updateOrCreate berdasarkan task_id dan student_id
        $submission = TaskSubmission::updateOrCreate(
            [
                'task_id' => $taskId,
                'student_id' => $student->id // Gunakan ID Student, bukan ID User
            ],
            [
                'file_path' => $path,
                'notes' => $request->notes,
                'submitted_at' => now(), // Update waktu pengumpulan
            ]
        );

        return back()->with('success', 'Tugas berhasil dikirim!');
    }
}