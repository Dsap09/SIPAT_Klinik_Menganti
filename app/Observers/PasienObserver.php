<?php

namespace App\Observers;

use App\Models\Pasien;
use App\Services\AuditLogger;

class PasienObserver
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function created(Pasien $pasien): void
    {
        $this->audit->catat('Pasien', "Tambah data pasien: {$pasien->Nama_Lengkap}");
    }

    public function updated(Pasien $pasien): void
    {
        $this->audit->catat('Pasien', "Ubah data pasien: {$pasien->Nama_Lengkap}");
    }
}
