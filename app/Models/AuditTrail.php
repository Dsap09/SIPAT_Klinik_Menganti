<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ID_Log', 'Waktu_Akses', 'Entitas_Terdampak', 'Deskripsi_Aksi', 'ID_Pengguna'])]
class AuditTrail extends Model
{
    protected $table = 'AUDIT_TRAIL';

    protected $primaryKey = 'ID_Log';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'Waktu_Akses' => 'datetime',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'ID_Pengguna', 'ID_Pengguna');
    }
}
