<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalAttendance extends Model
{
    protected $guarded = ['id'];

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}