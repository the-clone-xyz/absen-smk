<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kelas;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Attendance;
use App\Models\SystemSetting;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use App\Traits\AttendanceTokenTrait;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AdminController extends Controller
{
    use AttendanceTokenTrait;

    // ===============================================
    // DASHBOARD
    // ===============================================
    public function index()
    {
        // 1. Statistik Utama
        $stats = [
            'students' => Student::count(),
            'teachers' => Teacher::count(),
            'classes'  => Kelas::count(),
            'subjects' => Subject::count(),
        ];

        // 2. Chart Gender (Laki-laki vs Perempuan)
        $countL = Student::where('gender', 'L')->count();
        $countP = Student::where('gender', 'P')->count();

        $genderChart = [
            'labels' => ['Laki-laki', 'Perempuan'],
            'data'   => [(int) $countL, (int) $countP]
        ];

        // 3. Chart Kehadiran (7 Hari Terakhir)
        Carbon::setLocale('id');
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(6);
        $period = CarbonPeriod::create($startDate, $endDate);

        $attendanceData = Attendance::where('status', 'Hadir')
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->select(DB::raw('DATE(date) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->pluck('total', 'date');

        $chartLabels = [];
        $chartValues = [];

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $chartLabels[] = $date->isoFormat('dddd');
            $chartValues[] = $attendanceData[$dateString] ?? 0;
        }

        // 4. Chart Pertumbuhan Siswa (5 Tahun Terakhir)
        $studentGrowth = Student::select(DB::raw('YEAR(created_at) as year'), DB::raw('count(*) as total'))
            ->groupBy('year')
            ->orderBy('year', 'asc')
            ->limit(5)
            ->get();

        $growthChart = [
            'labels' => $studentGrowth->pluck('year'),
            'data'   => $studentGrowth->pluck('total'),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats'       => $stats,
            'genderChart' => $genderChart,
            'chart'       => ['labels' => $chartLabels, 'data' => $chartValues],
            'growthChart' => $growthChart,
        ]);
    }

    // ===============================================
    // MANAJEMEN USER & ROLE
    // ===============================================
    public function userManagement()
    {
        return Inertia::render('Admin/UserManagement', [
            'usersData' => User::orderBy('role')->get(),
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:student,teacher,admin']);
        $user->update(['role' => $request->role]);
        
        return back()->with('success', 'Peran user berhasil diperbarui!');
    }

    // ===============================================
    // MANAJEMEN MATA PELAJARAN
    // ===============================================
    public function subjectManagement()
    {
        return Inertia::render('Admin/Subjects/Index', [
            'subjects' => Subject::orderBy('name', 'asc')->get()
        ]);
    }

    public function storeSubject(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name',
            'code' => 'nullable|string|max:50|unique:subjects,code',
        ]);

        Subject::create($request->all());
        return Redirect::route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name,' . $subject->id,
            'code' => 'nullable|string|max:50|unique:subjects,code,' . $subject->id,
        ]);

        $subject->update($request->all());
        return Redirect::route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroySubject(Subject $subject)
    {
        try {
            $subject->delete();
            return Redirect::route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil dihapus.');
        } catch (\Exception $e) {
            return Redirect::route('admin.subjects.index')->with('error', 'Gagal menghapus. Mapel terikat data lain.');
        }
    }

    // ===============================================
    // MANAJEMEN KELAS
    // ===============================================
    public function classManagement()
    {
        return Inertia::render('Admin/Classes/Index', [
            'classes' => Kelas::orderBy('name', 'asc')->get()
        ]);
    }

    public function storeKelas(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:kelas,name',
            'level' => 'required|in:X,XI,XII',
        ]);

        Kelas::create($request->all());
        return Redirect::route('admin.classes.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function updateKelas(Request $request, Kelas $kelas)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:kelas,name,' . $kelas->id,
            'level' => 'required|in:X,XI,XII',
        ]);

        $kelas->update($request->all());
        return Redirect::route('admin.classes.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroyKelas(Kelas $kelas)
    {
        try {
            $kelas->delete();
            return Redirect::route('admin.classes.index')->with('success', 'Kelas berhasil dihapus.');
        } catch (\Exception $e) {
            return Redirect::route('admin.classes.index')->with('error', 'Gagal. Kelas masih memiliki siswa.');
        }
    }

    // ===============================================
    // MANAJEMEN JADWAL
    // ===============================================
    public function scheduleManagement(Request $request)
    {
        $query = Schedule::with(['kelas', 'subject', 'teacher.user'])
            ->orderByRaw("FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('start_time');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        return Inertia::render('Admin/Schedules/Index', [
            'schedules' => $query->get(),
            'classes'   => Kelas::orderBy('name')->get(),
            'subjects'  => Subject::orderBy('name')->get(),
            'teachers'  => Teacher::with('user')->get(),
            'filters'   => $request->only(['class_id'])
        ]);
    }

    public function storeSchedule(Request $request)
    {
        $request->validate([
            'class_id'   => 'required|exists:kelas,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day'        => 'required|string',
            'start_time' => 'required',
            'end_time'   => 'required|after:start_time',
        ]);

        Schedule::create($request->all());
        return back()->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function destroySchedule($id)
    {
        Schedule::findOrFail($id)->delete();
        return back()->with('success', 'Jadwal berhasil dihapus.');
    }

    // ===============================================
    // LAPORAN ABSENSI
    // ===============================================
    public function attendanceReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfMonth()->toDateString());
        $classId   = $request->input('class_id');
        $teacherId = $request->input('teacher_id');
        $targetRole = $request->input('role', 'student');

        $query = Attendance::with(['user.student.kelas', 'user.teacher'])
            ->whereBetween('date', [$startDate, $endDate])
            ->whereHas('user', function ($q) use ($targetRole) {
                $q->where('role', $targetRole);
            });

        if ($targetRole === 'student' && $classId) {
            $query->whereHas('user.student', fn($q) => $q->where('class_id', $classId));
        } elseif ($targetRole === 'teacher' && $teacherId) {
            $query->whereHas('user.teacher', fn($q) => $q->where('id', $teacherId));
        }

        $attendances = $query->latest('date')->get();

        // Mode Cetak
        if ($request->has('print')) {
            $filterLabel = 'Semua';
            if ($targetRole == 'student' && $classId) {
                $kelasData = Kelas::find($classId);
                $filterLabel = $kelasData ? $kelasData->name : '-';
            } elseif ($targetRole == 'teacher' && $teacherId) {
                $guru = Teacher::with('user')->find($teacherId);
                $filterLabel = $guru ? $guru->user->name : '-';
            }

            return view('print.attendance_report', [
                'attendances' => $attendances,
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'kelas_nama'  => $filterLabel,
                'role_label'  => $targetRole == 'student' ? 'Siswa' : 'Guru'
            ]);
        }

        return Inertia::render('Admin/Attendance/Report', [
            'attendances' => $attendances,
            'classes'     => Kelas::orderBy('name')->get(),
            'teachers'    => Teacher::with('user')->get(),
            'filters'     => [
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'class_id'   => $classId,
                'teacher_id' => $teacherId,
                'role'       => $targetRole,
            ]
        ]);
    }

    // ===============================================
    // PENGATURAN & QR
    // ===============================================
    public function qrGenerator()
    {
        return Inertia::render('Admin/Settings/QrGenerator', [
            'initialToken' => $this->generateAttendanceToken(0)
        ]);
    }

    public function getQrToken()
    {
        return response()->json(['token' => $this->generateAttendanceToken(0)]);
    }

    public function attendanceSettings()
    {
        return Inertia::render('Admin/Settings/Attendance', [
            'setting' => SystemSetting::first()
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'school_name'    => 'required|string',
            'latitude'       => 'required|numeric',
            'longitude'      => 'required|numeric',
            'radius_limit'   => 'required|integer',
            'clock_in_time'  => 'required',
            'clock_out_time' => 'required',
        ]);

        SystemSetting::updateOrCreate(['id' => 1], $validated);
        return back()->with('success', 'Pengaturan sistem berhasil diperbarui!');
    }
}