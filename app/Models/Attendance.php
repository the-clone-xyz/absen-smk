<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    // Izinkan semua kolom diisi (mass assignment)
    protected $fillable = [
        'user_id',
        'date',
        'time_in',
        'status',
        'description',
        'approval_status',
        'photo_path',
        'latitude',
        'longitude',
    ];

    // Relasi: Data absen ini milik siapa? (Milik User/Siswa)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}