<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PENGGUNA', function (Blueprint $table) {
            $table->string('ID_Pengguna')->primary();
            $table->string('Username')->unique();
            $table->string('Password');
            $table->string('Role');
            $table->string('Nama_Pengguna');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PENGGUNA');
    }
};
