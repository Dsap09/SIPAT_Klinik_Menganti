<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ID_Jadwal', 'Hari_Layanan', 'Jam_Mulai', 'Jam_Selesai', 'Kuota_Maksimal', 'Sisa_Kuota', 'ID_Poli', 'ID_Dokter'])]
class Jadwal extends Model
{
    protected $table = 'JADWAL';

    protected $primaryKey = 'ID_Jadwal';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class, 'ID_Poli', 'ID_Poli');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'ID_Dokter', 'ID_Dokter');
    }

    public function antrean(): HasMany
    {
        return $this->hasMany(Antrean::class, 'ID_Jadwal', 'ID_Jadwal');
    }

    public function tersedia(): bool
    {
        return $this->Sisa_Kuota > 0;
    }
}
