<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name')->default('SMK Tamansiswa');
            $table->string('academic_year')->default('2025/2026');
            $table->double('latitude')->default(-6.200000); // Default Jakarta
            $table->double('longitude')->default(106.816666);
            $table->integer('radius_limit')->default(50); // Dalam Meter
            $table->time('clock_in_time')->default('07:00:00');
            $table->time('clock_out_time')->default('15:00:00');
            $table->timestamps();
        });
    }
};
