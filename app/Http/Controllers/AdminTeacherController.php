<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class AdminTeacherController extends Controller
{
    // 1. DAFTAR GURU
    public function index(Request $request)
    {
        $query = Teacher::with('user');

        if ($request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%');
            })->orWhere('nip', 'like', '%'.$request->search.'%');
        }

        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => $query->latest()->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    // 2. FORM TAMBAH
    public function create()
    {
        return Inertia::render('Admin/Teachers/Create');
    }

    // 3. SIMPAN DATA (AUTO GENERATE AKUN)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|numeric|unique:teachers,nip',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:5120', // Max 5MB
        ]);

        DB::transaction(function () use ($request) {
            
            // A. Upload Foto (Jika ada)
            $avatarPath = null;
            if ($request->hasFile('photo')) {
                $avatarPath = $request->file('photo')->store('avatars', 'public');
            }

            // B. Buat Akun Login
            // Email: [NIP]@guru.smk.sch.id
            // Pass: [NIP]
            $user = User::create([
                'name' => $request->name,
                'email' => $request->nip . '@guru.smk.sch.id',
                'password' => Hash::make($request->nip),
                'role' => 'teacher',
                'avatar' => $avatarPath,
            ]);

            // C. Buat Profil Guru
            Teacher::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);
        });

        return Redirect::route('admin.teachers.index')->with('success', 'Guru berhasil ditambahkan.');
    }

    // 4. HAPUS GURU
    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        $user = User::findOrFail($teacher->user_id);
        $user->delete(); // Hapus user, otomatis hapus teacher (cascade)

        return back()->with('success', 'Data guru berhasil dihapus.');
    }
}