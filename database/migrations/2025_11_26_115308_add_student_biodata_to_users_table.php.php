<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // Kolom ID Siswa
        $table->string('nis')->unique()->nullable()->after('name');
        $table->string('nisn')->unique()->nullable()->after('nis');

        // Tautan ke Kelas (Foreign Key)
        $table->foreignId('class_id')
              ->nullable()
              ->after('role') // Setelah kolom role yang sudah ada
              ->constrained('classes') // Terhubung ke tabel 'classes'
              ->onDelete('set null'); // Jika kelas dihapus, class_id siswa menjadi NULL
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        // Hapus foreign key dulu sebelum menghapus kolom
        $table->dropConstrainedForeignId('class_id'); 
        
        // Hapus kolom
        $table->dropColumn(['nis', 'nisn']);
        // Kolom class_id sudah dihapus oleh dropConstrainedForeignId
    });
}
};
