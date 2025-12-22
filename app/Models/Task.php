<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // SAYA GANTI DARI $guarded MENJADI $fillable
    // Tujuannya agar nama kolomnya jelas tertulis dan aman.
    protected $fillable = [
        'teacher_id',
        'kelas_id',   // Pastikan di database namanya 'kelas_id' (bukan class_id)
        'subject_id',
        'title',
        'description',
        'deadline',
        'file',       // <--- INI PERBAIKANNYA.
                      // Ubah ini sesuai nama kolom di database kamu.
                      // Jika di database namanya 'file_path', tulis 'file_path'.
                      // Jika di database namanya 'file', tulis 'file'.
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    // --- RELASI ---

    public function teacher() {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    // Perhatikan 'class_id' di sini.
    // Jika di tabel tasks nama kolomnya 'kelas_id', ubah parameter kedua jadi 'kelas_id'.
    // Jika nama kolomnya 'class_id', biarkan seperti ini.
    public function kelas() {
        // Cek database: kalau foreign key-nya 'kelas_id', ganti parameter kedua:
        return $this->belongsTo(Kelas::class, 'kelas_id'); 
    }

    public function subject() {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function submissions() {
        return $this->hasMany(TaskSubmission::class);
    }
}