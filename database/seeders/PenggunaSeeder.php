<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use Illuminate\Database\Seeder;

class PenggunaSeeder extends Seeder
{
    public function run(): void
    {
        $pengguna = [
            ['ID_Pengguna' => 'USR-01', 'Username' => 'admin', 'Password' => 'password', 'Role' => Pengguna::ROLE_ADMIN, 'Nama_Pengguna' => 'Administrator Klinik'],
            ['ID_Pengguna' => 'USR-02', 'Username' => 'petugas', 'Password' => 'password', 'Role' => Pengguna::ROLE_PETUGAS, 'Nama_Pengguna' => 'Petugas Administrasi'],
            ['ID_Pengguna' => 'USR-03', 'Username' => 'manajemen', 'Password' => 'password', 'Role' => Pengguna::ROLE_MANAJEMEN, 'Nama_Pengguna' => 'Manajemen Klinik'],
            ['ID_Pengguna' => 'USR-04', 'Username' => 'dokter', 'Password' => 'password', 'Role' => Pengguna::ROLE_DOKTER, 'Nama_Pengguna' => 'Dokter Klinik'],
        ];

        foreach ($pengguna as $data) {
            Pengguna::updateOrCreate(['ID_Pengguna' => $data['ID_Pengguna']], $data);
        }
    }
}
