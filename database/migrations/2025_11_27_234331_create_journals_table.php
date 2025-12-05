<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->date('date'); // Tanggal mengajar
            $table->text('topic'); // Materi yang diajarkan
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->timestamps();
        });

        // Tabel Detail Absensi Per Mapel (Opsional tapi disarankan untuk ketelitian)
        Schema::create('journal_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('journals')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('status'); // Hadir, Bolos, Izin, Sakit
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_attendances');
        Schema::dropIfExists('journals');
    }
};
