<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskSubmission extends Model
{
    protected $guarded = [];

    // Relasi ke Tugas
    public function task() {
        return $this->belongsTo(Task::class);
    }

    // PERBAIKAN DI SINI:
    // Ubah User::class menjadi Student::class
    public function student() {
        return $this->belongsTo(Student::class, 'student_id');
    }
}