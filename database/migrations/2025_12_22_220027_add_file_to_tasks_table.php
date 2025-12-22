<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Kita tambahkan kolom 'file' (tipe string, boleh kosong)
            // Kita taruh setelah kolom 'deadline' biar rapi
            if (!Schema::hasColumn('tasks', 'file')) {
                $table->string('file')->nullable()->after('deadline');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'file')) {
                $table->dropColumn('file');
            }
        });
    }
};