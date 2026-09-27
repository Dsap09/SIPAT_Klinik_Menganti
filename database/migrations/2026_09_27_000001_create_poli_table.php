<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('POLI', function (Blueprint $table) {
            $table->string('ID_Poli')->primary();
            $table->string('Nama_Poli');
            $table->text('Deskripsi')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('POLI');
    }
};
