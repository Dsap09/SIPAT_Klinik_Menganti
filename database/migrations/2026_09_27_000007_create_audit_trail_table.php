<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('AUDIT_TRAIL', function (Blueprint $table) {
            $table->string('ID_Log')->primary();
            $table->dateTime('Waktu_Akses');
            $table->string('Entitas_Terdampak');
            $table->string('Deskripsi_Aksi');
            $table->string('ID_Pengguna')->nullable();

            $table->foreign('ID_Pengguna')->references('ID_Pengguna')->on('PENGGUNA');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('AUDIT_TRAIL');
    }
};
