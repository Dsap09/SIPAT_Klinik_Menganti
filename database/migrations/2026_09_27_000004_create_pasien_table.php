<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PASIEN', function (Blueprint $table) {
            $table->string('ID_Pasien')->primary();
            $table->string('No_RM')->nullable()->unique();
            $table->string('Nama_Lengkap');
            $table->date('Tgl_Lahir');
            $table->text('Alamat')->nullable();
            $table->string('Jenis_Pasien');
            $table->string('No_BPJS')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PASIEN');
    }
};
