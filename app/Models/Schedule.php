<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $guarded = [];

    // --- TAMBAHKAN BAGIAN INI ---

    // 1. Relasi ke Kelas
    public function kelas()
    {
        // Parameter kedua 'class_id' WAJIB ada karena nama kolom di tabel schedules Anda adalah 'class_id'
        // Jika tidak ditulis, Laravel akan mencari kolom 'kelas_id' dan error lagi.
        return $this->belongsTo(Kelas::class, 'class_id');
    }

    // 2. Relasi ke Mapel (Subject)
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    // 3. Relasi ke Guru (Teacher)
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}