<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kelas; // Sudah diubah dari Cclass
use App\Models\Subject; 
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect; 
use Illuminate\Support\Facades\DB; 
use App\Traits\AttendanceTokenTrait;

class AdminController extends Controller
{
    use AttendanceTokenTrait;

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
        return Inertia::render('Admin/Subjects/Index', [
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
        return Inertia::render('Admin/Classes/Index', [
            'classes' => $classes
        ]);
    }

    // METHOD BARU: Menyimpan Kelas Baru (CREATE)
    public function storeKelas(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:kelas,name',
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
            'name' => 'required|string|max:100|unique:kelas,name,' . $kelas->id,
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

    // ===============================================
    // 4. MANAJEMEN JADWAL (SCHEDULES)
    // ===============================================

    // Halaman Manajemen Jadwal
    public function scheduleManagement(Request $request)
    {
        // Ambil Data Pendukung untuk Dropdown
        $classes = Kelas::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        // Ambil Guru beserta nama User-nya
        $teachers = \App\Models\Teacher::with('user')->get(); 

        // Filter Jadwal Berdasarkan Kelas yang Dipilih (Jika ada)
        $query = \App\Models\Schedule::with(['class', 'subject', 'teacher.user'])
                    ->orderByRaw("FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
                    ->orderBy('start_time');

        if ($request->has('class_id') && $request->class_id != null) {
            $query->where('class_id', $request->class_id);
        }

        $schedules = $query->get();

        return Inertia::render('Admin/Schedules/Index', [
            'schedules' => $schedules,
            'classes' => $classes,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'filters' => $request->only(['class_id'])
        ]);
    }

    // Simpan Jadwal Baru
    public function storeSchedule(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:kelas,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        \App\Models\Schedule::create($request->all());

        return back()->with('success', 'Jadwal berhasil ditambahkan.');
    }

    // Hapus Jadwal
    public function destroySchedule($id)
    {
        \App\Models\Schedule::findOrFail($id)->delete();
        return back()->with('success', 'Jadwal berhasil dihapus.');
    }


// ===============================================
    // 5. LAPORAN ABSENSI (DIPERBARUI)
    // ===============================================

    public function attendanceReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
        $classId = $request->input('class_id');
        
        // TANGKAP ROLE (Default ke student jika tidak ada)
        $targetRole = $request->input('role', 'student'); 

        // Query Data Absensi
        $query = \App\Models\Attendance::with(['user.student.kelas', 'user.teacher'])
                    ->whereBetween('date', [$startDate, $endDate])
                    ->whereHas('user', function($q) use ($targetRole) {
                        $q->where('role', $targetRole); // <--- FILTER ROLE DISINI
                    });

        // Filter Kelas (Hanya jika role = student)
        if ($targetRole === 'student' && $classId) {
            $query->whereHas('user.student', function($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        $attendances = $query->latest('date')->get();
        $classes = Kelas::orderBy('name')->get(); // Data kelas tetap dikirim

        // Mode Cetak (Blade)
        if ($request->has('print')) {
            return view('print.attendance_report', [
                'attendances' => $attendances,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'kelas_nama' => ($targetRole == 'student' && $classId) ? Kelas::find($classId)->name : 'Semua',
                'role_label' => $targetRole == 'student' ? 'Siswa' : 'Guru'
            ]);
        }

        // Mode Tampilan Web (Vue)
        return Inertia::render('Admin/Attendance/Report', [
            'attendances' => $attendances,
            'classes' => $classes,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'class_id' => $classId,
                'role' => $targetRole, // Kirim info role ke frontend
            ]
        ]);
    }

    // ===============================================
    // 6. PENGATURAN QR CODE (GENERATOR)
    // ===============================================

    // Halaman Tampilan QR
    public function qrGenerator()
    {
        // Generate token awal
        $initialToken = $this->generateAttendanceToken(0);

        return Inertia::render('Admin/Settings/QrGenerator', [
            'initialToken' => $initialToken
        ]);
    }

    // API Endpoint untuk Refresh Token (Dipanggil via AJAX/Fetch)
    public function getQrToken()
    {
        $token = $this->generateAttendanceToken(0);
        return response()->json(['token' => $token]);
    }

    
}