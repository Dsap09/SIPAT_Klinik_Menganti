<?php

namespace App\Models;

use App\Observers\PasienObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable([
    'ID_Pasien',
    'No_RM',
    'NIK',
    'Nama_Lengkap',
    'Tempat_Lahir',
    'Tgl_Lahir',
    'Jenis_Kelamin',
    'Alamat',
    'No_Telepon',
    'Jenis_Pasien',
    'No_BPJS',
    'Agama',
    'Pekerjaan',
    'Status_Pernikahan',
    'Pendidikan',
    'Penanggung_Jawab',
])]
#[ObservedBy([PasienObserver::class])]
class Pasien extends Authenticatable
{
    public const JENIS_UMUM = 'UMUM';

    public const JENIS_BPJS = 'BPJS';

    public const JENIS_KELAMIN = ['Laki-laki', 'Perempuan'];

    public const JENIS_PEMBAYARAN = [self::JENIS_UMUM, self::JENIS_BPJS];

    protected $table = 'PASIEN';

    protected $primaryKey = 'ID_Pasien';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'Tgl_Lahir' => 'date',
        ];
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function antrean(): HasMany
    {
        return $this->hasMany(Antrean::class, 'ID_Pasien', 'ID_Pasien');
    }
}
