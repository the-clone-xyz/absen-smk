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
    Schema::table('students', function (Blueprint $table) {
        // Menambah kolom gender (L = Laki-laki, P = Perempuan)
        // Diletakkan setelah NISN agar rapi
        $table->enum('gender', ['L', 'P'])->nullable()->after('nisn');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            //
        });
    }
};
