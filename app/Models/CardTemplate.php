<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardTemplate extends Model
{
    use HasFactory;

    // Tambahkan kolom yang boleh diisi di sini
    protected $fillable = [
        'name',
        'background_path',
        'background_back_path',
        'elements',
        'is_active', // <--- INI WAJIB ADA AGAR ERROR HILANG
    ];

    // Opsional: Cast elements ke array/json otomatis agar mudah dipakai
    protected $casts = [
        'elements' => 'array',
        'is_active' => 'boolean',
    ];
}