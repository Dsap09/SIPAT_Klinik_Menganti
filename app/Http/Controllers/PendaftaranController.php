<?php

namespace App\Http\Controllers;

use App\Models\Antrean;
use App\Models\Jadwal;
use App\Models\Pasien;
use App\Services\QueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class PendaftaranController extends Controller
{
    public function __construct(private readonly QueueService $queue) {}

    public function beranda(): View
    {
        return view('beranda');
    }

    public function form(): View
    {
        $jadwal = Jadwal::query()
            ->with(['poli', 'dokter'])
            ->where('Sisa_Kuota', '>', 0)
            ->orderBy('Hari_Layanan')
            ->orderBy('Jam_Mulai')
            ->get();

        return view('pendaftaran.form', ['jadwal' => $jadwal]);
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
        $antrean = Antrean::query()
            ->with(['pasien', 'jadwal.poli', 'jadwal.dokter'])
            ->where('No_Antrean', $noAntrean)
            ->orderByDesc('Tanggal_Kunjungan')
            ->firstOrFail();

        return view('pendaftaran.status', ['antrean' => $antrean]);
    }
}
