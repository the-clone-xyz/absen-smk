<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeacherController; 
use App\Http\Controllers\AdminController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
    
    // Dashboard Guru
    Route::get('/dashboard', [TeacherController::class, 'index'])->name('dashboard');
    
    // Approval Izin (PATCH untuk update status approved/rejected)
    Route::patch('/absensi/{id}/approve', [TeacherController::class, 'updateStatus'])->name('attendance.approve');
    
    // Rute untuk mengambil token QR secara real-time
    Route::get('/qr-token', [TeacherController::class, 'getQrToken'])->name('qr.token'); 

    // Halaman Persetujuan Izin (Tabel Khusus Pending)
    Route::get('/approval/izin', [TeacherController::class, 'showPending'])->name('approval.index');
});

// =========================================================================
// ZONA 3: ADMIN (Role: admin)
// =========================================================================
    Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'userManagement'])->name('users.index');
        Route::patch('/users/{user}/update-role', [AdminController::class, 'updateRole'])->name('users.updateRole');
        Route::get('/subjects', [AdminController::class, 'subjectManagement'])->name('subjects.index');
        Route::get('/settings/attendance', [AdminController::class, 'attendanceSettings'])->name('settings.attendance');
        Route::get('/settings/qr-generator', [AdminController::class, 'qrGenerator'])->name('settings.qr');
    });

// =========================================================================
// ZONA UMUM (Profile, bisa diakses semua role)
// =========================================================================
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.delete'); // FIX: name()->delete() -> name()->delete
    });

// 4. MANAJEMEN KELAS (CRUD)
    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/', [AdminController::class, 'classManagement'])->name('index'); // Menjadi: admin.classes.index
        Route::post('/', [AdminController::class, 'storeKelas'])->name('store');
        Route::patch('/{kelas}', [AdminController::class, 'updateKelas'])->name('update');
        Route::delete('/{kelas}', [AdminController::class, 'destroyKelas'])->name('destroy');
    });

require __DIR__.'/auth.php';