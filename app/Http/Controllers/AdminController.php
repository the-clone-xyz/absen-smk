<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kelas; // Sudah diubah dari Cclass
use App\Models\Subject; 
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect; 
use Illuminate\Support\Facades\DB; 

class AdminController extends Controller
{
    // Halaman Dashboard Admin (Menu Utama & Statistik Overview)
    public function index()
    {
        // Panggilan ke Model sekarang akan berhasil
        $totalStudents = User::where('role', 'student')->count();
        $totalTeachers = User::where('role', 'teacher')->count();
        $totalClasses = Kelas::count();
        $totalSubjects = Subject::count();
        
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'students' => $totalStudents,
                'teachers' => $totalTeachers,
                'classes' => $totalClasses,
                'subjects' => $totalSubjects,
            ],
        ]);
    }

    // ===============================================
    // 1. MANAJEMEN USER & ROLE
    // ===============================================

    // Halaman Pengaturan Role/User
    public function userManagement()
    {
        $users = User::orderBy('role')->get(); 
        
        return Inertia::render('Admin/UserManagement', [
            'usersData' => $users,
        ]);
    }

    // Method untuk Mengubah Role User
    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:student,teacher,admin']);
        $user->update(['role' => $request->role]);
        return back()->with('success', 'Peran user berhasil diperbarui!');
    }

    // ===============================================
    // 2. MANAJEMEN MATA PELAJARAN (CRUD)
    // ===============================================

    // Halaman Pengaturan Mata Pelajaran (READ)
    public function subjectManagement()
    {
        $subjects = Subject::orderBy('name', 'asc')->get();
        return Inertia::render('Admin/SubjectManagement', [
            'subjects' => $subjects
        ]);
    }

    // Menyimpan Mata Pelajaran Baru (CREATE)
    public function storeSubject(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name',
            'code' => 'nullable|string|max:50|unique:subjects,code',
        ], [
            'name.unique' => 'Mata Pelajaran dengan nama ini sudah ada.',
            'code.unique' => 'Kode Mapel ini sudah digunakan.'
        ]);

        Subject::create($request->all());

        return Redirect::route('admin.subjects.index')->with('success', 'Mata pelajaran baru berhasil ditambahkan.');
    }

    // Mengubah Mata Pelajaran (UPDATE)
    public function updateSubject(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name,' . $subject->id,
            'code' => 'nullable|string|max:50|unique:subjects,code,' . $subject->id,
        ]);

        $subject->update($request->all());

        return Redirect::route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    // Menghapus Mata Pelajaran (DELETE)
    public function destroySubject(Subject $subject)
    {
        try {
            $subject->delete();
            return Redirect::route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil dihapus.');
        } catch (\Exception $e) {
            return Redirect::route('admin.subjects.index')->with('error', 'Gagal menghapus. Mapel mungkin terikat dengan Jadwal atau Nilai Siswa.');
        }
    }

    // ===============================================
    // 3. MANAJEMEN KELAS (CRUD)
    // ===============================================

    // Halaman Pengaturan Kelas (READ)
    public function classManagement()
    {
        $classes = Kelas::orderBy('name', 'asc')->get();
        return Inertia::render('Admin/ClassManagement', [
            'classes' => $classes
        ]);
    }

    // METHOD BARU: Menyimpan Kelas Baru (CREATE)
    public function storeKelas(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:classes,name',
            'level' => 'required|in:X,XI,XII', // Contoh level kelas
        ], [
            'name.unique' => 'Kelas dengan nama ini sudah ada.',
            'level.required' => 'Wajib memilih level kelas (X, XI, atau XII).'
        ]);

        Kelas::create($request->all());

        return Redirect::route('admin.classes.index')->with('success', 'Kelas baru berhasil ditambahkan.');
    }

    // METHOD BARU: Mengubah Kelas (UPDATE)
    public function updateKelas(Request $request, Kelas $kelas)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:classes,name,' . $kelas->id,
            'level' => 'required|in:X,XI,XII',
        ]);

        $kelas->update($request->all());

        return Redirect::route('admin.classes.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    // METHOD BARU: Menghapus Kelas (DELETE)
    public function destroyKelas(Kelas $kelas)
    {
        try {
            $kelas->delete();
            return Redirect::route('admin.classes.index')->with('success', 'Kelas berhasil dihapus.');
        } catch (\Exception $e) {
            // Perlu dicek apakah ada siswa yang terikat dengan kelas ini
            return Redirect::route('admin.classes.index')->with('error', 'Gagal menghapus. Kelas masih memiliki siswa terdaftar.');
        }
    }
}