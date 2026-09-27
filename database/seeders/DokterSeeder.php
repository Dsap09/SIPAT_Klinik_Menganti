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
            ['ID_Dokter' => 'DOK-03', 'Nama_Dokter' => 'dr. Rina Kusuma, Sp.A', 'Spesialisasi' => 'Spesialis Anak', 'ID_Poli' => 'POLI-03'],
            ['ID_Dokter' => 'DOK-04', 'Nama_Dokter' => 'Bd. Nur Aini', 'Spesialisasi' => 'Bidan', 'ID_Poli' => 'POLI-04'],
        ];

        foreach ($dokter as $data) {
            Dokter::updateOrCreate(['ID_Dokter' => $data['ID_Dokter']], $data);
        }
    }
}
