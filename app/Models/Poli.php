<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ID_Poli', 'Nama_Poli', 'Deskripsi'])]
class Poli extends Model
{
    protected $table = 'POLI';

    protected $primaryKey = 'ID_Poli';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    public function dokter(): HasMany
    {
        return $this->hasMany(Dokter::class, 'ID_Poli', 'ID_Poli');
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'ID_Poli', 'ID_Poli');
    }
}
