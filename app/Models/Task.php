<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $guarded = [];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function teacher() {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function kelas() {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function subject() {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function submissions() {
        return $this->hasMany(TaskSubmission::class);
    }
}