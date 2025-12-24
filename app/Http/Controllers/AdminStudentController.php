<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminStudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['user', 'kelas']);

        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })->orWhere('nis', 'like', '%' . $request->search . '%');
        }

        return Inertia::render('Admin/Students/Index', [
            'students' => $query->latest()->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Students/Create', [
            'classes' => Kelas::orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'numeric', 'unique:students,nis'],
            'nisn' => ['nullable', 'numeric', 'unique:students,nisn'],
            'gender' => ['required', 'in:L,P'],
            'pob' => ['required', 'string', 'max:50'],
            'dob' => ['required', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'class_id' => ['required', 'exists:kelas,id'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        DB::transaction(function () use ($request) {
            $avatarPath = null;
            if ($request->hasFile('photo')) {
                $avatarPath = $request->file('photo')->store('avatars', 'public');
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->nis . '@smk.sch.id',
                'password' => Hash::make($request->nis),
                'role' => 'student',
                'avatar' => $avatarPath,
            ]);

            Student::create([
                'user_id' => $user->id,
                'class_id' => $request->class_id,
                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'gender' => $request->gender,
                'pob' => $request->pob,
                'dob' => $request->dob,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);
        });

        return Redirect::route('admin.students.index')->with('success', 'Data Siswa berhasil disimpan!');
    }

    public function edit(Student $student)
    {
        $student->load(['user', 'kelas']);

        return Inertia::render('Admin/Students/Edit', [
            'student' => $student,
            'classes' => Kelas::orderBy('name')->get()
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($student->user_id)],
            'nis' => ['required', 'numeric', Rule::unique('students')->ignore($student->id)],
            'nisn' => ['nullable', 'numeric', Rule::unique('students')->ignore($student->id)],
            'class_id' => 'required|exists:kelas,id',
            'gender' => ['required', 'in:L,P'],
            'pob' => 'required|string',
            'dob' => 'required|date',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        DB::transaction(function () use ($request, $student) {
            if ($request->hasFile('photo')) {
                if ($student->user->avatar) {
                    Storage::disk('public')->delete($student->user->avatar);
                }
                $avatarPath = $request->file('photo')->store('avatars', 'public');
                $student->user->update(['avatar' => $avatarPath]);
            }

            $student->user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            $student->update([
                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'class_id' => $request->class_id,
                'gender' => $request->gender,
                'pob' => $request->pob,
                'dob' => $request->dob,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);
        });

        return Redirect::route('admin.students.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $user = User::findOrFail($student->user_id);

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return Redirect::route('admin.students.index')->with('success', 'Data siswa berhasil dihapus.');
    }
}