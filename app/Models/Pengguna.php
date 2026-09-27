<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ID_Pengguna', 'Username', 'Password', 'Role', 'Nama_Pengguna'])]
#[Hidden(['Password'])]
class Pengguna extends Model
{
    public const ROLE_ADMIN = 'Admin';

    public const ROLE_PETUGAS = 'Petugas';

    public const ROLE_MANAJEMEN = 'Manajemen';

    public const ROLE_DOKTER = 'Dokter';

    protected $table = 'PENGGUNA';

    protected $primaryKey = 'ID_Pengguna';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'Password' => 'hashed',
        ];
    }

    public function auditTrail(): HasMany
    {
        return $this->hasMany(AuditTrail::class, 'ID_Pengguna', 'ID_Pengguna');
    }
}
