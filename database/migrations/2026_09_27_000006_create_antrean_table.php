<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ANTREAN', function (Blueprint $table) {
            $table->string('ID_Antrean')->primary();
            $table->string('No_Antrean');
            $table->date('Tanggal_Kunjungan');
            $table->time('Estimasi_Waktu')->nullable();
            $table->string('Status')->default('Menunggu');
            $table->timestamp('Waktu_CheckIn')->nullable();
            $table->string('ID_Pasien');
            $table->string('ID_Jadwal');

            $table->foreign('ID_Pasien')->references('ID_Pasien')->on('PASIEN');
            $table->foreign('ID_Jadwal')->references('ID_Jadwal')->on('JADWAL');
            $table->unique(['Tanggal_Kunjungan', 'No_Antrean']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ANTREAN');
    }
};
