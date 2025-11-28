<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;
    
    // Pastikan nama tabel benar
    protected $table = 'kelas';
    
    protected $guarded = []; 

    // PERBAIKAN RELASI DISINI:
    public function students()
    {
        // Sebelumnya mungkin: hasMany(User::class) -> SALAH
        // Seharusnya: hasMany(Student::class) -> BENAR
        return $this->hasMany(Student::class, 'class_id');
    }
}