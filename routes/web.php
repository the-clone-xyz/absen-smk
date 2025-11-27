<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeacherController; 
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController; // Pastikan ini ada
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- HALAMAN DEPAN (LANDING PAGE) ---
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// =========================================================================
// ZONA 1: SISWA (Role: student)
// =========================================================================
Route::middleware(['auth', 'verified', 'role:student'])->group(function () {
    
    // Dashboard Siswa
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Fitur Absensi
    Route::get('/absen', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/absen', [AttendanceController::class, 'store'])->name('attendance.store');
    
    // Fitur Izin
    Route::get('/izin', [AttendanceController::class, 'izin'])->name('attendance.izin');
    
    // Fitur Riwayat
    Route::get('/rekap', [AttendanceController::class, 'rekap'])->name('attendance.rekap');

    // Fitur Kartu Pelajar
    Route::get('/kartu-pelajar', function () {
        return Inertia::render('Student/Card');
    })->name('student.card');

});

// =========================================================================
// ZONA 2: GURU (Role: teacher)
// =========================================================================
Route::middleware(['auth', 'verified', 'role:teacher'])->prefix('guru')->name('teacher.')->group(function () {
    
    Route::get('/dashboard', [TeacherController::class, 'index'])->name('dashboard');
    Route::patch('/absensi/{id}/approve', [TeacherController::class, 'updateStatus'])->name('attendance.approve');
    Route::get('/qr-token', [TeacherController::class, 'getQrToken'])->name('qr.token'); 
    Route::get('/approval/izin', [TeacherController::class, 'showPending'])->name('approval.index');

});

// =========================================================================
// ZONA 3: ADMIN (Role: admin)
// =========================================================================
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // 1. Dashboard Admin
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // 2. Manajemen User
    Route::get('/users', [AdminController::class, 'userManagement'])->name('users.index');
    Route::patch('/users/{user}/update-role', [AdminController::class, 'updateRole'])->name('users.updateRole');
    
    // 3. Manajemen Mata Pelajaran (CRUD Lengkap)
    Route::prefix('subjects')->name('subjects.')->group(function () {
        Route::get('/', [AdminController::class, 'subjectManagement'])->name('index');
        Route::post('/', [AdminController::class, 'storeSubject'])->name('store');
        Route::patch('/{subject}', [AdminController::class, 'updateSubject'])->name('update');
        Route::delete('/{subject}', [AdminController::class, 'destroySubject'])->name('destroy');
    });

    

    // 4. Manajemen Kelas (CRUD Lengkap) - SUDAH DIPINDAH KE DALAM SINI
    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/', [AdminController::class, 'classManagement'])->name('index');
        Route::post('/', [AdminController::class, 'storeKelas'])->name('store');
        Route::patch('/{kelas}', [AdminController::class, 'updateKelas'])->name('update');
        Route::delete('/{kelas}', [AdminController::class, 'destroyKelas'])->name('destroy');
    });

    //print absen
    Route::get('/reports/attendance', [AdminController::class, 'attendanceReport'])->name('attendance.report');

    // MANAJEMEN GURU
    Route::resource('teachers', \App\Http\Controllers\AdminTeacherController::class)->names('teachers');
    // 5. Manajemen Siswa (Resource)
    Route::resource('students', StudentController::class);

    // 5. MANAJEMEN JADWAL
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', [AdminController::class, 'scheduleManagement'])->name('index');
        Route::post('/', [AdminController::class, 'storeSchedule'])->name('store');
        Route::delete('/{id}', [AdminController::class, 'destroySchedule'])->name('destroy');
    });

// 6. QR CODE GENERATOR (Real-time)
    Route::get('/settings/qr-generator', [AdminController::class, 'qrGenerator'])->name('settings.qr');
    
    // API untuk fetch token baru (Admin)
    Route::get('/settings/get-qr-token', [AdminController::class, 'getQrToken'])->name('settings.get_qr');

    // 7. Pengaturan Sistem (Placeholder dulu)
    Route::get('/settings/attendance', function() { return Inertia::render('Admin/Settings/Attendance'); })->name('settings.attendance');
});

// =========================================================================
// ZONA UMUM (Profile)
// =========================================================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.delete');
});

require __DIR__.'/auth.php';