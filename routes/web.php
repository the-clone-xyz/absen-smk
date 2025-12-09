<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeacherController; 
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\FileViewerController;
use App\Http\Controllers\TaskController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Models\SystemSetting;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $setting = SystemSetting::first();
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'schoolName' => $setting ? $setting->school_name : 'SMK TAMANSISWA',
        'phpVersion' => PHP_VERSION,
    ]);
});

// ZONA 1: SISWA
Route::middleware(['auth', 'verified', 'role:student'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/izin', [AttendanceController::class, 'izin'])->name('attendance.izin');
    Route::get('/rekap', [AttendanceController::class, 'rekap'])->name('attendance.rekap');
    Route::get('/kartu-pelajar', function () {
        return Inertia::render('Student/Card');
    })->name('student.card');
});

// ZONA 2: GURU
Route::middleware(['auth', 'verified', 'role:teacher'])->prefix('guru')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherController::class, 'index'])->name('dashboard');
    Route::patch('/absensi/{id}/approve', [TeacherController::class, 'updateStatus'])->name('attendance.approve');
    Route::get('/qr-token', [TeacherController::class, 'getQrToken'])->name('qr.token'); 
    Route::get('/approval/izin', [TeacherController::class, 'showPending'])->name('approval.index');
    Route::get('/kelas/{scheduleId}', [TeacherController::class, 'showClass'])->name('classroom.show');
    Route::post('/jurnal', [TeacherController::class, 'storeJournal'])->name('journal.store');
    Route::get('/class-qr-token/{scheduleId}', [TeacherController::class, 'getClassQrToken'])->name('classroom.qr_token');
    Route::get('/class-data/{scheduleId}', [TeacherController::class, 'getClassData'])->name('classroom.data');
});

// ZONA 3: ADMIN
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'userManagement'])->name('users.index');
    Route::patch('/users/{user}/update-role', [AdminController::class, 'updateRole'])->name('users.updateRole');
    
    Route::prefix('subjects')->name('subjects.')->group(function () {
        Route::get('/', [AdminController::class, 'subjectManagement'])->name('index');
        Route::post('/', [AdminController::class, 'storeSubject'])->name('store');
        Route::patch('/{subject}', [AdminController::class, 'updateSubject'])->name('update');
        Route::delete('/{subject}', [AdminController::class, 'destroySubject'])->name('destroy');
    });
    
    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/', [AdminController::class, 'classManagement'])->name('index');
        Route::post('/', [AdminController::class, 'storeKelas'])->name('store');
        Route::patch('/{kelas}', [AdminController::class, 'updateKelas'])->name('update');
        Route::delete('/{kelas}', [AdminController::class, 'destroyKelas'])->name('destroy');
    });

    Route::get('/reports/attendance', [AdminController::class, 'attendanceReport'])->name('attendance.report');
    Route::get('/rekap-guru', [AttendanceController::class, 'rekapGuru'])->name('rekap.guru');

    Route::resource('teachers', \App\Http\Controllers\AdminTeacherController::class)->names('teachers');
    Route::resource('students', StudentController::class);
    
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', [AdminController::class, 'scheduleManagement'])->name('index');
        Route::post('/', [AdminController::class, 'storeSchedule'])->name('store');
        Route::delete('/{id}', [AdminController::class, 'destroySchedule'])->name('destroy');
    });
    
    Route::get('/settings/qr-generator', [AdminController::class, 'qrGenerator'])->name('settings.qr');
    Route::get('/settings/get-qr-token', [AdminController::class, 'getQrToken'])->name('settings.get_qr');
    Route::get('/settings/attendance', [AdminController::class, 'attendanceSettings'])->name('settings.attendance');
    Route::patch('/settings/attendance', [AdminController::class, 'updateSettings'])->name('settings.update');
});

// ZONA UMUM
Route::middleware('auth')->group(function () {
    Route::get('/absen', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/absen', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.delete');
    Route::get('/viewer', [FileViewerController::class, 'show'])->name('viewer.show');
    Route::get('/viewer/download', [FileViewerController::class, 'download'])->name('viewer.download');
});

// =========================================================================
// FITUR TUGAS (E-LEARNING)
// =========================================================================
// Pastikan baris ini ada di paling atas file web.php

Route::middleware(['auth'])->group(function () {
    // 1. List Tugas (Siswa melihat daftar PR disini)
    Route::get('/tugas', [TaskController::class, 'index'])->name('tasks.index');
    
    // 2. Guru Buat Tugas (Dari Modal di Halaman Kelas)
    Route::post('/tugas', [TaskController::class, 'store'])
        ->name('tasks.store')
        ->middleware('role:teacher');
    
    // 3. Siswa Kirim Jawaban
    Route::post('/tugas/{id}/submit', [TaskController::class, 'submit'])
        ->name('tasks.submit')
        ->middleware('role:student');

    // 4. Guru Memberi Nilai (Fitur Baru)
    Route::post('/tugas/submission/{id}/grade', [TaskController::class, 'gradeSubmission'])
        ->name('tasks.grade')
        ->middleware('role:teacher');

    // Update Tugas (Pakai POST karena ada upload file, nanti di spoofing _method: PUT)
    Route::post('/tugas/{id}/update', [TaskController::class, 'update'])->name('tasks.update')->middleware('role:teacher');
    
    // Hapus Tugas
    Route::delete('/tugas/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy')->middleware('role:teacher');
    
    // Simpan Nilai
    Route::post('/tugas/submission/{id}/grade', [TaskController::class, 'gradeSubmission'])->name('tasks.grade')->middleware('role:teacher');
});

require __DIR__.'/auth.php';