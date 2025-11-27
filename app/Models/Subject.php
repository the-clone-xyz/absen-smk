<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;
    
    // Tambahkan baris ini agar Count() dan CRUD bekerja
    protected $guarded = []; 
}