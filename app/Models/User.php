<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tambahkan untuk relasi

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Role user (student, teacher, admin)
        'avatar', // Path untuk foto profil (dari migrasi sebelumnya)
        'nis',    // Nomor Induk Siswa (dari migrasi sebelumnya)
        'nisn',   // Nomor Induk Siswa Nasional (dari migrasi sebelumnya)
        'class_id', // Foreign key ke tabel 'classes' (dari migrasi sebelumnya)
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function currentClass(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'class_id');
    }
}