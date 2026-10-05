<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('PASIEN', function (Blueprint $table) {
            $table->string('NIK')->nullable()->unique();
            $table->string('Tempat_Lahir')->nullable();
            $table->string('Jenis_Kelamin')->nullable();
            $table->string('No_Telepon')->nullable()->unique();
            $table->string('Agama')->nullable();
            $table->string('Pekerjaan')->nullable();
            $table->string('Status_Pernikahan')->nullable();
            $table->string('Pendidikan')->nullable();
            $table->string('Penanggung_Jawab')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('PASIEN', function (Blueprint $table) {
            $table->dropUnique(['NIK']);
            $table->dropUnique(['No_Telepon']);
            $table->dropColumn([
                'NIK',
                'Tempat_Lahir',
                'Jenis_Kelamin',
                'No_Telepon',
                'Agama',
                'Pekerjaan',
                'Status_Pernikahan',
                'Pendidikan',
                'Penanggung_Jawab',
            ]);
        });
    }
};
