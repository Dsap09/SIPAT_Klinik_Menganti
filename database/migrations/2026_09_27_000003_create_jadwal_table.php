<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('JADWAL', function (Blueprint $table) {
            $table->string('ID_Jadwal')->primary();
            $table->string('Hari_Layanan');
            $table->time('Jam_Mulai');
            $table->time('Jam_Selesai');
            $table->integer('Kuota_Maksimal');
            $table->integer('Sisa_Kuota');
            $table->string('ID_Poli');
            $table->string('ID_Dokter');

            $table->foreign('ID_Poli')->references('ID_Poli')->on('POLI');
            $table->foreign('ID_Dokter')->references('ID_Dokter')->on('DOKTER');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('JADWAL');
    }
};
