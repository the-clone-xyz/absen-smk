<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

// Models
use App\Models\SystemSetting;

// Controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileViewerController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminStudentController;
use App\Http\Controllers\AdminTeacherController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\EbookController;
use App\Http\Controllers\CardTemplateController;
use App\Http\Controllers\CardController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- HALAMAN DEPAN ---
Route::get('/', function () {
    $setting = SystemSetting::first();
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'schoolName'     => $setting ? $setting->school_name : 'SMKS TAMANSISWA LUBUK PAKAM 2',
        'phpVersion'     => PHP_VERSION,
    ]);
});

// --- CENTRAL DASHBOARD REDIRECT ---
// Mengarahkan user ke dashboard spesifik berdasarkan role
Route::get('/dashboard', function () {
    $user = Auth::user();
    if (!$user) return redirect()->route('login');
    
    // Pastikan role valid sebelum redirect
    $role = $user->role ?? 'student'; 
    return redirect()->route($role . '.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// =========================================================================
// ZONA 1: SISWA (Student)
// =========================================================================
Route::middleware(['auth', 'verified', 'role:student'])
    ->prefix('siswa')
    ->name('student.')
    ->group(function () {
        
        // Dashboard & Kartu
        Route::get('/dashboard', [StudentController::class, 'index'])->name('dashboard');
       Route::get('/kartu-pelajar', [CardController::class, 'index'])->name('card');
        
        // Absensi (Izin & Rekap)
        Route::controller(AttendanceController::class)->group(function () {
            Route::get('/izin', 'izin')->name('attendance.izin');
            Route::get('/rekap', 'rekap')->name('attendance.rekap');
        });

        // Kelas & Tugas
        Route::get('/kelas/{scheduleId}', [StudentController::class, 'showClass'])
            ->whereNumber('scheduleId')
            ->name('classroom.show');

        // Tugas (Detail & Submit)
        Route::get('/tugas/{id}', [StudentController::class, 'showTask'])
            ->whereNumber('id')
            ->name('tasks.show');
            
        // Security: Batasi submit tugas max 10x per menit untuk mencegah spam
        Route::post('/tugas/{id}/submit', [StudentController::class, 'submitTask'])
            ->middleware('throttle:10,1')
            ->whereNumber('id')
            ->name('tasks.submit');
    });


// =========================================================================
// ZONA 2: GURU (Teacher)
// =========================================================================
    Route::middleware(['auth', 'verified', 'role:teacher'])
    ->prefix('guru')
    ->group(function () {

        // --- GROUP A: Dashboard & Manajemen Kelas ---
        Route::name('teacher.')->group(function () {
            Route::get('/dashboard', [TeacherController::class, 'index'])->name('dashboard');
            Route::post('/simpan-nilai', [TeacherController::class, 'storeGrades'])->name('grades.store');
           // 1. DETAIL KELAS (Data Induk/Anggota) -> ClassroomShow.vue
            Route::get('/kelas-induk/{id}', [TeacherController::class, 'show'])
                ->whereNumber('id')
                ->name('classroom.show');

            // 2. SESI MENGAJAR (Harian/Sesi) -> Classroom.vue (Kode yang Bapak kirim)
            Route::get('/sesi-mengajar/{scheduleId}', [TeacherController::class, 'showClass'])
                ->whereNumber('scheduleId')
                ->name('classroom.session'); // Kita beri nama rute ini 'classroom.session'

            // 3. ABSENSI & APPROVAL
            Route::get('/qr-token', [TeacherController::class, 'getQrToken'])->name('qr.token');
            Route::get('/approval/izin', [TeacherController::class, 'showPending'])->name('approval.index');
            Route::patch('/absensi/{id}/approve', [TeacherController::class, 'updateStatus'])
                ->whereNumber('id')
                ->name('attendance.approve');

            // 4. API / DATA PENDUKUNG
            Route::post('/jurnal', [TeacherController::class, 'storeJournal'])->name('journal.store');
            Route::get('/class-qr-token/{scheduleId}', [TeacherController::class, 'getClassQrToken'])->name('classroom.qr_token');
            Route::get('/class-data/{scheduleId}', [TeacherController::class, 'getClassData'])->name('classroom.data');
        });

        // --- GROUP B: Manajemen Tugas (Jangan Diganggu) ---
        Route::controller(TaskController::class)->group(function () {
            Route::get('/tugas/{task}', 'show')->whereNumber('task')->name('teacher.tasks.show');
            Route::post('/tugas', 'store')->name('tasks.store');
            Route::post('/tugas/{id}/update', 'update')->whereNumber('id')->name('tasks.update');
            Route::delete('/tugas/{id}', 'destroy')->whereNumber('id')->name('tasks.destroy');
            Route::post('/tugas/submission/{id}/grade', 'gradeSubmission')->whereNumber('id')->name('tasks.grade');
        });

    });

// =========================================================================
// ZONA 3: ADMIN (Administrator)
// =========================================================================
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        
          // Halaman Desainer
        Route::get('/card-designer', [CardTemplateController::class, 'index'])->name('card.designer');
        // Simpan Desain
        Route::post('/card-designer', [CardTemplateController::class, 'store'])->name('card.store');

        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        // Manajemen User & Role
        Route::get('/users', [AdminController::class, 'userManagement'])->name('users.index');
        Route::patch('/users/{user}/update-role', [AdminController::class, 'updateRole'])->name('users.updateRole');

        // Resource Controllers (Otomatis index, create, store, edit, update, destroy)
        Route::resource('teachers', AdminTeacherController::class);
        Route::resource('students', AdminStudentController::class);

        // Manajemen Akademik (Subject, Class, Schedule)
        Route::prefix('subjects')->name('subjects.')->controller(AdminController::class)->group(function () {
            Route::get('/', 'subjectManagement')->name('index');
            Route::post('/', 'storeSubject')->name('store');
            Route::patch('/{subject}', 'updateSubject')->name('update');
            Route::delete('/{subject}', 'destroySubject')->name('destroy');
        });

        Route::prefix('classes')->name('classes.')->controller(AdminController::class)->group(function () {
            Route::get('/', 'classManagement')->name('index');
            Route::post('/', 'storeKelas')->name('store');
            Route::patch('/{kelas}', 'updateKelas')->name('update');
            Route::delete('/{kelas}', 'destroyKelas')->name('destroy');
        });

        Route::prefix('schedules')->name('schedules.')->controller(AdminController::class)->group(function () {
            Route::get('/', 'scheduleManagement')->name('index');
            Route::post('/', 'storeSchedule')->name('store');
            Route::delete('/{id}', 'destroySchedule')->whereNumber('id')->name('destroy');
        });

        // Laporan
        Route::controller(AdminController::class)->group(function() {
            Route::get('/reports/attendance', 'attendanceReport')->name('attendance.report');
            Route::get('/rekap-guru', 'rekapGuru')->name('rekap.guru');
        });

        // Pengaturan Sistem & QR
        Route::prefix('settings')->name('settings.')->controller(AdminController::class)->group(function () {
            Route::get('/qr-generator', 'qrGenerator')->name('qr');
            Route::get('/get-qr-token', 'getQrToken')->name('get_qr');
            Route::get('/attendance', 'attendanceSettings')->name('attendance');
            Route::patch('/attendance', 'updateSettings')->name('update');
        });

    });


// =========================================================================
// ZONA UMUM (User Login: Guru/Siswa/Admin)
// =========================================================================
Route::middleware(['auth', 'verified'])->group(function () {
    
    
    // --- FITUR SCAN ABSENSI (CRITICAL) ---
    // Security: Throttle 6 request per menit untuk mencegah spam scan
    Route::controller(AttendanceController::class)->middleware('throttle:6,1')->group(function () {
        Route::get('/absen', 'index')->name('attendance.index');
        Route::post('/absen', 'store')->name('attendance.store');
    });

    // Profile Management
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.delete');
    });

    // File Viewer (Secure Preview)
    Route::controller(FileViewerController::class)->group(function () {
        Route::get('/viewer', 'show')->name('viewer.show');
        Route::get('/viewer/download', 'download')->name('viewer.download');
    });

    Route::get('/library', [EbookController::class, 'index'])->name('ebooks.index');
    
    // KEMBALI KE POST BIASA
    Route::post('/ebooks', [EbookController::class, 'store'])->name('ebooks.store');
    
    Route::delete('/ebooks/{id}', [EbookController::class, 'destroy'])->name('ebooks.destroy');
    Route::get('/ebooks/{ebook}/read', [EbookController::class, 'read'])->name('ebooks.read');
    Route::get('/ebooks/{ebook}/read-flip', [EbookController::class, 'readFlip'])->name('ebooks.readFlip');
});

require __DIR__.'/auth.php';