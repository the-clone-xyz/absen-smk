<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $guarded = [];

    public function schedule() { return $this->belongsTo(Schedule::class); }
    public function teacher() { return $this->belongsTo(Teacher::class); }
    public function attendances()
    {
        return $this->hasMany(JournalAttendance::class); 
    }
}
