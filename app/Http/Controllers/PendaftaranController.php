<?php

namespace App\Http\Controllers;

use App\Models\Antrean;
use App\Models\Jadwal;
use App\Models\Poli;
use App\Services\AuditLogger;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class PendaftaranController extends Controller
{
    public function __construct(
        private readonly QueueService $queue,
        private readonly AuditLogger $audit,
    ) {}

    public function beranda(): View
    {
        return view('beranda', [
            'poli' => Poli::query()->withCount('dokter')->orderBy('Nama_Poli')->get(),
            'jadwal' => Jadwal::tersediaUrutHari(),
        ]);
    }

    public function cekStatus(Request $request): RedirectResponse
    {
        $nomor = strtoupper(trim((string) $request->query('no', '')));

        if (! preg_match('/^A-\d{1,4}$/', $nomor)) {
            return redirect()
                ->route('beranda')
                ->with('error', 'Masukkan nomor antrean dengan format yang benar, misalnya A-001.');
        }

        return redirect()->route('pendaftaran.status', $nomor);
    }

    public function form(): View|RedirectResponse
    {
        if (auth('pasien')->check()) {
            return redirect()->route('pasien.daftar');
        }

        return view('pendaftaran.gerbang');
    }

    public function status(string $noAntrean): View
    {
        return view('pendaftaran.status', ['antrean' => $this->cariAntrean($noAntrean)]);
    }

    public function statusData(string $noAntrean): JsonResponse
    {
        $antrean = $this->cariAntrean($noAntrean);

        return response()->json([
            'no_antrean' => $antrean->No_Antrean,
            'status' => $antrean->Status,
            'estimasi_waktu' => $antrean->Estimasi_Waktu ? substr($antrean->Estimasi_Waktu, 0, 5) : null,
            'checkin' => $antrean->Waktu_CheckIn?->format('H:i'),
            'pesan' => "Antrean {$antrean->No_Antrean} berstatus {$antrean->Status}.",
            'diperbarui_pada' => now()->format('H:i:s'),
        ]);
    }

    public function batal(string $noAntrean): RedirectResponse
    {
        $antrean = $this->cariAntrean($noAntrean);

        try {
            $this->queue->batalkan($antrean);
        } catch (RuntimeException $e) {
            return redirect()
                ->route('pendaftaran.status', $noAntrean)
                ->with('error', $e->getMessage());
        }

        $this->audit->catat('Antrean', "Batalkan antrean {$antrean->No_Antrean}");

        return redirect()
            ->route('pendaftaran.status', $noAntrean)
            ->with('sukses', 'Pendaftaran dibatalkan. Kuota dikembalikan untuk pasien lain.');
    }

    public function formJadwalUlang(string $noAntrean): View
    {
        $antrean = $this->cariAntrean($noAntrean);

        if (! $antrean->bisaDibatalkan()) {
            abort(403, 'Antrean ini tidak dapat dijadwalkan ulang.');
        }

        return view('pendaftaran.jadwal-ulang', [
            'antrean' => $antrean,
            'jadwal' => Jadwal::tersediaUrutHari(),
        ]);
    }

    public function jadwalUlang(Request $request, string $noAntrean): RedirectResponse
    {
        $antrean = $this->cariAntrean($noAntrean);

        $data = $request->validate([
            'ID_Jadwal' => ['required', 'string', 'exists:JADWAL,ID_Jadwal'],
        ]);

        $baru = Jadwal::findOrFail($data['ID_Jadwal']);

        try {
            $antreanBaru = $this->queue->jadwalkanUlang($antrean, $baru);
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['ID_Jadwal' => $e->getMessage()]);
        }

        $this->audit->catat('Antrean', "Jadwalkan ulang {$antrean->No_Antrean} menjadi {$antreanBaru->No_Antrean}");

        return redirect()
            ->route('pendaftaran.status', $antreanBaru->No_Antrean)
            ->with('sukses', "Pendaftaran dijadwalkan ulang. Nomor antrean baru: {$antreanBaru->No_Antrean}.");
    }

    private function cariAntrean(string $noAntrean): Antrean
    {
        return Antrean::query()
            ->with(['pasien', 'jadwal.poli', 'jadwal.dokter'])
            ->where('No_Antrean', $noAntrean)
            ->orderByDesc('Tanggal_Kunjungan')
            ->firstOrFail();
    }
}
