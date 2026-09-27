<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('DOKTER', function (Blueprint $table) {
            $table->string('ID_Dokter')->primary();
            $table->string('Nama_Dokter');
            $table->string('Spesialisasi')->nullable();
            $table->string('ID_Poli');

            $table->foreign('ID_Poli')->references('ID_Poli')->on('POLI');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DOKTER');
    }
};
