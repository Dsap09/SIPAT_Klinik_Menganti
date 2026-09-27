<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ID_Antrean', 'No_Antrean', 'Tanggal_Kunjungan', 'Estimasi_Waktu', 'Status', 'Waktu_CheckIn', 'ID_Pasien', 'ID_Jadwal'])]
class Antrean extends Model
{
    public const STATUS_MENUNGGU = 'Menunggu';

    public const STATUS_DILAYANI = 'Dilayani';

    public const STATUS_SELESAI = 'Selesai';

    public const STATUS_BATAL = 'Batal';

    protected $table = 'ANTREAN';

    protected $primaryKey = 'ID_Antrean';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'Tanggal_Kunjungan' => 'date',
            'Waktu_CheckIn' => 'datetime',
        ];
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'ID_Pasien', 'ID_Pasien');
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'ID_Jadwal', 'ID_Jadwal');
    }

    public function bisaDibatalkan(): bool
    {
        return $this->Status === self::STATUS_MENUNGGU && $this->Waktu_CheckIn === null;
    }

    public function scopePadaTanggal(Builder $query, string $tanggal): Builder
    {
        return $query->whereBetween('Tanggal_Kunjungan', [
            $tanggal.' 00:00:00',
            $tanggal.' 23:59:59',
        ]);
    }
}
