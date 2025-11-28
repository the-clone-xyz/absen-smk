<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    // 1. PERBAIKAN ERROR MASS ASSIGNMENT
    // Baris ini mengizinkan semua kolom untuk diisi
    protected $guarded = []; 

    // 2. DEFINISI RELASI (PENTING UNTUK TABEL NANTI)
    
    // Relasi ke Kelas (Contoh: XII RPL 1)
    public function class()
    {
        return $this->belongsTo(Kelas::class, 'class_id');
    }

    // Relasi ke Mapel (Contoh: Matematika)
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    // Relasi ke Guru (Contoh: Pak Budi)
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}