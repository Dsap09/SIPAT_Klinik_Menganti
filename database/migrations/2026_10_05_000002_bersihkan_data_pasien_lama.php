<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Membersihkan data pasien lama (tanpa format No RM baru) beserta antreannya.
     * Migrasi ini sengaja tidak dapat dibalik (down no-op): data historis dihapus
     * agar seluruh pasien memakai format RM-{TAHUN}-{URUTAN} yang baru.
     */
    public function up(): void
    {
        DB::table('ANTREAN')->delete();
        DB::table('PASIEN')->delete();

        DB::table('JADWAL')->update(['Sisa_Kuota' => DB::raw('Kuota_Maksimal')]);
    }

    public function down(): void
    {
        // Tidak ada pemulihan data yang dihapus.
    }
};
