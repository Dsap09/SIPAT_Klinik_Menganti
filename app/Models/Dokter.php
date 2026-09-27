<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ID_Dokter', 'Nama_Dokter', 'Spesialisasi', 'ID_Poli'])]
class Dokter extends Model
{
    protected $table = 'DOKTER';

    protected $primaryKey = 'ID_Dokter';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class, 'ID_Poli', 'ID_Poli');
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'ID_Dokter', 'ID_Dokter');
    }
}
