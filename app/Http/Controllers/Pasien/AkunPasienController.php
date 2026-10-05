<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use App\Services\AuditLogger;
use App\Services\RekamMedisService;
use App\Support\NomorTelepon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AkunPasienController extends Controller
{
    public function __construct(
        private readonly RekamMedisService $rekamMedis,
        private readonly AuditLogger $audit,
    ) {}

    public function formRegister(): View
    {
        return view('pasien.register');
    }

    public function simpanRegister(Request $request): RedirectResponse
    {
        $request->merge(['No_Telepon' => NomorTelepon::normalisasi((string) $request->input('No_Telepon'))]);

        $data = $request->validate([
            'Nama_Lengkap' => ['required', 'string', 'max:255'],
            'Tempat_Lahir' => ['required', 'string', 'max:255'],
            'Tgl_Lahir' => ['required', 'date', 'before:today'],
            'Jenis_Kelamin' => ['required', 'in:'.implode(',', Pasien::JENIS_KELAMIN)],
            'NIK' => ['required', 'digits:16', 'unique:PASIEN,NIK'],
            'Alamat' => ['required', 'string', 'max:500'],
            'No_Telepon' => ['required', 'string', 'max:25', 'regex:/^[0-9+\-\s()]{8,25}$/', 'unique:PASIEN,No_Telepon'],
            'Jenis_Pasien' => ['required', 'in:'.implode(',', Pasien::JENIS_PEMBAYARAN)],
            'No_BPJS' => ['nullable', 'required_if:Jenis_Pasien,'.Pasien::JENIS_BPJS, 'string', 'max:50'],
            'Agama' => ['nullable', 'string', 'max:50'],
            'Pekerjaan' => ['nullable', 'string', 'max:100'],
            'Status_Pernikahan' => ['nullable', 'string', 'max:50'],
            'Pendidikan' => ['nullable', 'string', 'max:100'],
            'Penanggung_Jawab' => ['nullable', 'string', 'max:255'],
        ]);

        $pasien = Pasien::create([
            'ID_Pasien' => (string) Str::uuid(),
            'No_RM' => $this->rekamMedis->nomorBaru(),
            'NIK' => $data['NIK'],
            'Nama_Lengkap' => $data['Nama_Lengkap'],
            'Tempat_Lahir' => $data['Tempat_Lahir'],
            'Tgl_Lahir' => $data['Tgl_Lahir'],
            'Jenis_Kelamin' => $data['Jenis_Kelamin'],
            'Alamat' => $data['Alamat'],
            'No_Telepon' => $data['No_Telepon'],
            'Jenis_Pasien' => $data['Jenis_Pasien'],
            'No_BPJS' => $data['Jenis_Pasien'] === Pasien::JENIS_BPJS ? ($data['No_BPJS'] ?? null) : null,
            'Agama' => $data['Agama'] ?? null,
            'Pekerjaan' => $data['Pekerjaan'] ?? null,
            'Status_Pernikahan' => $data['Status_Pernikahan'] ?? null,
            'Pendidikan' => $data['Pendidikan'] ?? null,
            'Penanggung_Jawab' => $data['Penanggung_Jawab'] ?? null,
        ]);

        $this->audit->catat('Akun', "Registrasi akun pasien: {$pasien->No_RM} ({$pasien->Nama_Lengkap})");

        auth('pasien')->login($pasien);
        $request->session()->regenerate();

        return redirect()
            ->route('pasien.dashboard')
            ->with('sukses', "Pendaftaran berhasil. Nomor rekam medis Anda: {$pasien->No_RM}. Simpan nomor ini untuk berobat berikutnya.");
    }

    public function formLogin(): View
    {
        return view('pasien.login');
    }

    public function masuk(Request $request): RedirectResponse
    {
        $request->merge(['No_Telepon' => NomorTelepon::normalisasi((string) $request->input('No_Telepon'))]);

        $data = $request->validate([
            'No_RM' => ['nullable', 'string', 'max:50'],
            'No_Telepon' => ['nullable', 'string', 'max:25'],
            'Tgl_Lahir' => ['nullable', 'date'],
        ]);

        $terisi = collect(['No_RM', 'No_Telepon', 'Tgl_Lahir'])
            ->filter(fn (string $kolom): bool => filled($data[$kolom] ?? null))
            ->count();

        if ($terisi < 2) {
            throw ValidationException::withMessages([
                'No_RM' => 'Isi minimal 2 dari 3 data: nomor RM, nomor HP, atau tanggal lahir.',
            ]);
        }

        $pasien = $this->cariPasien($data);

        if (! $pasien) {
            throw ValidationException::withMessages([
                'No_RM' => 'Data pasien tidak ditemukan atau tidak cocok. Periksa kembali data Anda.',
            ]);
        }

        auth('pasien')->login($pasien);
        $request->session()->regenerate();

        $this->audit->catat('Akun', "Login pasien: {$pasien->No_RM} ({$pasien->Nama_Lengkap})");

        return redirect()->intended(route('pasien.dashboard'));
    }

    public function keluar(Request $request): RedirectResponse
    {
        $pasien = auth('pasien')->user();

        if ($pasien instanceof Pasien) {
            $this->audit->catat('Akun', "Logout pasien: {$pasien->No_RM}");
        }

        auth('pasien')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda')->with('sukses', 'Anda telah keluar dari akun pasien.');
    }

    private function cariPasien(array $data): ?Pasien
    {
        if (filled($data['No_RM'] ?? null)) {
            $pasien = Pasien::query()->where('No_RM', $data['No_RM'])->first();
        } else {
            $pasien = Pasien::query()->where('No_Telepon', $data['No_Telepon'])->first();
        }

        if (! $pasien) {
            return null;
        }

        if (filled($data['No_Telepon'] ?? null) && $pasien->No_Telepon !== $data['No_Telepon']) {
            return null;
        }

        if (filled($data['Tgl_Lahir'] ?? null) && $pasien->Tgl_Lahir?->toDateString() !== $data['Tgl_Lahir']) {
            return null;
        }

        return $pasien;
    }
}
