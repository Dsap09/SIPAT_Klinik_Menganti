<?php

namespace App\Http\Controllers;

use App\Models\Antrean;
use App\Models\Jadwal;
use App\Models\Pasien;
use App\Services\AuditLogger;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
        return view('beranda');
    }

    public function form(): View
    {
        return view('pendaftaran.form', ['jadwal' => $this->jadwalTersedia()]);
    }

    public function simpan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ID_Jadwal' => ['required', 'string', 'exists:JADWAL,ID_Jadwal'],
            'jenis' => ['required', 'in:baru,lama'],
            'Nama_Lengkap' => ['required_if:jenis,baru', 'nullable', 'string', 'max:255'],
            'Tgl_Lahir' => ['required_if:jenis,baru', 'nullable', 'date', 'before:today'],
            'Alamat' => ['nullable', 'string', 'max:500'],
            'No_RM' => ['required_if:jenis,lama', 'nullable', 'string', 'max:50'],
        ]);

        $jadwal = Jadwal::findOrFail($data['ID_Jadwal']);

        if ($data['jenis'] === 'lama') {
            $pasien = Pasien::where('No_RM', $data['No_RM'])->first();

            if (! $pasien) {
                throw ValidationException::withMessages([
                    'No_RM' => 'No RM tidak ditemukan. Periksa kembali atau daftar sebagai pasien baru.',
                ]);
            }
        } else {
            $pasien = new Pasien([
                'ID_Pasien' => (string) Str::uuid(),
                'Nama_Lengkap' => $data['Nama_Lengkap'],
                'Tgl_Lahir' => $data['Tgl_Lahir'],
                'Alamat' => $data['Alamat'] ?? null,
                'Jenis_Pasien' => Pasien::JENIS_UMUM,
            ]);
        }

        try {
            $antrean = $this->queue->daftar($jadwal, $pasien);
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['ID_Jadwal' => $e->getMessage()]);
        }

        return redirect()->route('pendaftaran.status', $antrean->No_Antrean);
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
            'jadwal' => $this->jadwalTersedia(),
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

    private function jadwalTersedia()
    {
        return Jadwal::query()
            ->with(['poli', 'dokter'])
            ->where('Sisa_Kuota', '>', 0)
            ->orderBy('Hari_Layanan')
            ->orderBy('Jam_Mulai')
            ->get();
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
