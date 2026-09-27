<?php

namespace App\Models;

use App\Observers\PasienObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ID_Pasien', 'No_RM', 'Nama_Lengkap', 'Tgl_Lahir', 'Alamat', 'Jenis_Pasien', 'No_BPJS'])]
#[ObservedBy([PasienObserver::class])]
class Pasien extends Model
{
    public const JENIS_UMUM = 'UMUM';

    public const JENIS_BPJS = 'BPJS';

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

    public function antrean(): HasMany
    {
        return $this->hasMany(Antrean::class, 'ID_Pasien', 'ID_Pasien');
    }
}
