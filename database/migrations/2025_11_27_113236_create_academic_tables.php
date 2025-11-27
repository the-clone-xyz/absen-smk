<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Kelas (Master)
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // XII RPL 1
            $table->string('level')->nullable(); // XII
            $table->timestamps();
        });

        // 2. Tabel Mata Pelajaran (Master)
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->nullable()->unique();
            $table->timestamps();
        });

        // 3. Tabel Guru (Terhubung ke User)
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nip')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        // 4. Tabel Siswa (Terhubung ke User & Kelas)
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('kelas')->onDelete('set null');
            
            $table->string('nis')->unique();
            $table->string('nisn')->unique()->nullable();
            $table->string('pob')->nullable(); // Tempat Lahir
            $table->date('dob')->nullable();   // Tanggal Lahir
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        // 5. Tabel Jadwal (Menghubungkan Kelas, Mapel, Guru)
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->string('day'); 
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('students');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('kelas');
    }
};