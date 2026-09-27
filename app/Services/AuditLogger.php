<?php

namespace App\Services;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditLogger
{
    public function catat(string $entitasTerdampak, string $deskripsiAksi, ?string $idPengguna = null): void
    {
        AuditTrail::create([
            'ID_Log' => (string) Str::uuid(),
            'Waktu_Akses' => now(),
            'Entitas_Terdampak' => $entitasTerdampak,
            'Deskripsi_Aksi' => $deskripsiAksi,
            'ID_Pengguna' => $idPengguna ?? Auth::id(),
        ]);
    }
}
