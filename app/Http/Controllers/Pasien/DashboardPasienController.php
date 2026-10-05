<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\Antrean;
use App\Models\Pasien;
use App\Support\NomorTelepon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardPasienController extends Controller
{
    public function dashboard(): View
    {
        $pasien = $this->pasien();

        return view('pasien.dashboard', [
            'pasien' => $pasien,
            'antreanAktif' => $pasien->antrean()
                ->with(['jadwal.poli', 'jadwal.dokter'])
                ->padaTanggal(today()->toDateString())
                ->whereIn('Status', [Antrean::STATUS_MENUNGGU, Antrean::STATUS_DILAYANI])
                ->orderBy('No_Antrean')
                ->get(),
            'totalKunjungan' => $pasien->antrean()->count(),
            'totalSelesai' => $pasien->antrean()->where('Status', Antrean::STATUS_SELESAI)->count(),
        ]);
    }

    public function riwayat(): View
    {
        $pasien = $this->pasien();

        return view('pasien.riwayat', [
            'pasien' => $pasien,
            'riwayat' => $pasien->antrean()
                ->with(['jadwal.poli', 'jadwal.dokter'])
                ->orderByDesc('Tanggal_Kunjungan')
                ->orderByDesc('No_Antrean')
                ->paginate(10),
        ]);
    }

    public function profil(): View
    {
        return view('pasien.profil', ['pasien' => $this->pasien()]);
    }

    public function simpanProfil(Request $request): RedirectResponse
    {
        $pasien = $this->pasien();

        $request->merge(['No_Telepon' => NomorTelepon::normalisasi((string) $request->input('No_Telepon'))]);

        $data = $request->validate([
            'No_Telepon' => [
                'required', 'string', 'max:25', 'regex:/^[0-9+\-\s()]{8,25}$/',
                Rule::unique('PASIEN', 'No_Telepon')->ignore($pasien->ID_Pasien, 'ID_Pasien'),
            ],
            'Alamat' => ['required', 'string', 'max:500'],
            'Agama' => ['nullable', 'string', 'max:50'],
            'Pekerjaan' => ['nullable', 'string', 'max:100'],
            'Status_Pernikahan' => ['nullable', 'string', 'max:50'],
            'Pendidikan' => ['nullable', 'string', 'max:100'],
            'Penanggung_Jawab' => ['nullable', 'string', 'max:255'],
        ]);

        $pasien->update($data);

        return redirect()
            ->route('pasien.profil')
            ->with('sukses', 'Data profil berhasil diperbarui.');
    }

    private function pasien(): Pasien
    {
        /** @var Pasien $pasien */
        $pasien = auth('pasien')->user();

        return $pasien;
    }
}
