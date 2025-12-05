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
            'nip' => 'nullable|numeric|unique:teachers,nip',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:5120', // Max 5MB
        ]);

        DB::transaction(function () use ($request) {

            // Jika NIP ada, pakai NIP. Jika kosong, pakai No HP.
            $loginCredential = $request->nip ? $request->nip : $request->phone;

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
                'email' => $loginCredential . '@guru.smk.sch.id', // Bisa NIP atau NoHP
                'password' => Hash::make($loginCredential),       // Password sama dengan username
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

    // 5. UPDATE GURU
    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);
        $user = User::findOrFail($teacher->user_id);

        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|unique:teachers,nip,' . $teacher->id, // Ignore current ID
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:5120',
            'password_option' => 'nullable|in:manual,random', // Pilihan Reset Password
            'new_password' => 'nullable|required_if:password_option,manual|min:6',
        ]);

        DB::transaction(function () use ($request, $teacher, $user) {
            
            // A. Update Foto (Jika ada)
            if ($request->hasFile('photo')) {
                $avatarPath = $request->file('photo')->store('avatars', 'public');
                $user->update(['avatar' => $avatarPath]);
            }

            // B. Reset Password (Jika dipilih)
            if ($request->password_option) {
                $newPass = ($request->password_option === 'random') ? 'guru123' : $request->new_password; // Random default guru123 dulu biar gampang
                $user->update(['password' => Hash::make($newPass)]);
            }

            // C. Update Data User & Teacher
            $user->update(['name' => $request->name]);
            $teacher->update([
                'nip' => $request->nip,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);
        });

        return back()->with('success', 'Data guru berhasil diperbarui.');
    }
}