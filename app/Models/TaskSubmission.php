<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskSubmission extends Model
{
    protected $guarded = [];

    public function task() {
        return $this->belongsTo(Task::class);
    }

    public function student() {
        return $this->belongsTo(User::class, 'student_id');
    }
}