<?php

namespace Database\Seeders;

use App\Models\Poli;
use Illuminate\Database\Seeder;

class PoliSeeder extends Seeder
{
    public function run(): void
    {
        $poli = [
            ['ID_Poli' => 'POLI-01', 'Nama_Poli' => 'Poli Umum', 'Deskripsi' => 'Pelayanan pemeriksaan umum'],
            ['ID_Poli' => 'POLI-02', 'Nama_Poli' => 'Poli Gigi', 'Deskripsi' => 'Pelayanan kesehatan gigi dan mulut'],
        ];

        foreach ($poli as $data) {
            Poli::updateOrCreate(['ID_Poli' => $data['ID_Poli']], $data);
        }
    }
}
