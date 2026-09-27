<?php

namespace Database\Seeders;

use App\Models\Jadwal;
use Illuminate\Database\Seeder;

class JadwalSeeder extends Seeder
{
    public function run(): void
    {
        $jadwal = [
            ['ID_Jadwal' => 'JDW-01', 'Hari_Layanan' => 'Senin', 'Jam_Mulai' => '08:00', 'Jam_Selesai' => '12:00', 'Kuota_Maksimal' => 20, 'Sisa_Kuota' => 20, 'ID_Poli' => 'POLI-01', 'ID_Dokter' => 'DOK-01'],
            ['ID_Jadwal' => 'JDW-02', 'Hari_Layanan' => 'Selasa', 'Jam_Mulai' => '08:00', 'Jam_Selesai' => '12:00', 'Kuota_Maksimal' => 15, 'Sisa_Kuota' => 15, 'ID_Poli' => 'POLI-02', 'ID_Dokter' => 'DOK-02'],
            ['ID_Jadwal' => 'JDW-03', 'Hari_Layanan' => 'Rabu', 'Jam_Mulai' => '13:00', 'Jam_Selesai' => '16:00', 'Kuota_Maksimal' => 10, 'Sisa_Kuota' => 10, 'ID_Poli' => 'POLI-01', 'ID_Dokter' => 'DOK-01'],
        ];

        foreach ($jadwal as $data) {
            Jadwal::updateOrCreate(['ID_Jadwal' => $data['ID_Jadwal']], $data);
        }
    }
}
