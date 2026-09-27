<?php

namespace Database\Seeders;

use App\Models\Dokter;
use Illuminate\Database\Seeder;

class DokterSeeder extends Seeder
{
    public function run(): void
    {
        $dokter = [
            ['ID_Dokter' => 'DOK-01', 'Nama_Dokter' => 'dr. Andi Pratama', 'Spesialisasi' => 'Dokter Umum', 'ID_Poli' => 'POLI-01'],
            ['ID_Dokter' => 'DOK-02', 'Nama_Dokter' => 'drg. Sari Melati', 'Spesialisasi' => 'Dokter Gigi', 'ID_Poli' => 'POLI-02'],
        ];

        foreach ($dokter as $data) {
            Dokter::updateOrCreate(['ID_Dokter' => $data['ID_Dokter']], $data);
        }
    }
}
