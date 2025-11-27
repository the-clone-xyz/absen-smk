<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Kelas; // Pastikan Model Kelas sudah ada
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    // 1. HALAMAN DAFTAR SISWA
    public function index(Request $request)
    {
       // Ambil data siswa + user + kelas
        $query = Student::with(['user', 'kelas']);

        // Fitur Pencarian (Search)
        if ($request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%');
            })->orWhere('nis', 'like', '%'.$request->search.'%');
        }

        // Render ke file Vue yang baru
        return Inertia::render('Admin/Students/Index', [
            'students' => $query->latest()->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    // 2. HALAMAN FORM TAMBAH
    public function create()
    {
        return Inertia::render('Admin/Students/Create', [
            'classes' => Kelas::orderBy('name')->get() // Kirim data kelas untuk dropdown
        ]);
    }

    // 3. PROSES SIMPAN (CREATE)
    public function store(Request $request)
    {
       // 1. Validasi Input (GUNAKAN ARRAY AGAR LEBIH AMAN)
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'numeric', 'unique:students,nis'],
            'nisn' => ['nullable', 'numeric', 'unique:students,nisn'],
            'pob' => ['required', 'string', 'max:50'],
            'dob' => ['required', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'class_id' => ['required', 'exists:kelas,id'], // Pastikan tabelnya 'kelas'
            
            // Perbaikan di sini: Gunakan Array
            'photo' => ['nullable', 'image', 'max:5120'], 
        ]);

        DB::transaction(function () use ($request) {
            
            // A. Handle Upload Foto
            $avatarPath = null;
            if ($request->hasFile('photo')) {
                $avatarPath = $request->file('photo')->store('avatars', 'public');
            }

            // B. Buat Akun Login (User)
            $user = User::create([
                'name' => $request->name,
                'email' => $request->nis . '@smk.sch.id', // Email otomatis
                'password' => Hash::make($request->nis), // Password default = NIS
                'role' => 'student',
                'avatar' => $avatarPath, // Simpan path foto di sini
            ]);

            // C. Buat Biodata Lengkap (Student)
            Student::create([
                'user_id' => $user->id,
                'class_id' => $request->class_id,
                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'pob' => $request->pob,
                'dob' => $request->dob,
                'phone' => $request->phone,
            ]);
        });

        return Redirect::route('admin.students.index')->with('success', 'Data Siswa berhasil disimpan!');
    }

    // 4. HAPUS SISWA
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $user = User::findOrFail($student->user_id);
        
        // Hapus User (Otomatis menghapus Student karena cascade)
        $user->delete(); 

        return Redirect::route('admin.students.index')->with('success', 'Data siswa berhasil dihapus.');
    }
}