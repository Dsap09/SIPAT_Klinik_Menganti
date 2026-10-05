<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Pasien;
use App\Services\AuditLogger;
use App\Services\QueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class PendaftaranPasienController extends Controller
{
    public function __construct(
        private readonly QueueService $queue,
        private readonly AuditLogger $audit,
    ) {}

    public function form(): View
    {
        /** @var Pasien $pasien */
        $pasien = auth('pasien')->user();

        return view('pasien.daftar', [
            'pasien' => $pasien,
            'jadwal' => Jadwal::tersediaUrutHari(),
        ]);
    }

    public function simpan(Request $request): RedirectResponse
    {
        /** @var Pasien $pasien */
        $pasien = auth('pasien')->user();

        if ($pasien->Jenis_Pasien === Pasien::JENIS_BPJS) {
            return redirect()
                ->route('pasien.dashboard')
                ->with('error', 'Pasien BPJS mengambil nomor antrean melalui Mobile JKN di loket. Akun ini tetap bisa dipakai untuk memantau riwayat kunjungan.');
        }

        $data = $request->validate([
            'ID_Jadwal' => ['required', 'string', 'exists:JADWAL,ID_Jadwal'],
        ]);

        $jadwal = Jadwal::findOrFail($data['ID_Jadwal']);

        try {
            $antrean = $this->queue->daftar($jadwal, $pasien);
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['ID_Jadwal' => $e->getMessage()]);
        }

        $this->audit->catat('Antrean', "Daftar berobat online {$antrean->No_Antrean} oleh pasien {$pasien->No_RM}");

        return redirect()
            ->route('pendaftaran.status', $antrean->No_Antrean)
            ->with('sukses', "Pendaftaran berhasil. Nomor antrean Anda: {$antrean->No_Antrean}.");
    }
}
