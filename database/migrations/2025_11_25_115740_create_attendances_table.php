<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('attendances', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Relasi ke tabel users (siswa)
        $table->date('date');       // Tanggal absen
        $table->time('time_in');    // Jam masuk
        $table->string('status');   // Hadir, Telat, atau Izin
        $table->string('photo_path')->nullable(); // Lokasi file foto
        $table->double('latitude')->nullable();   // Koordinat Latitude
        $table->double('longitude')->nullable();  // Koordinat Longitude
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
