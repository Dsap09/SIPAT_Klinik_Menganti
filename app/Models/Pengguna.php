<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['ID_Pengguna', 'Username', 'Password', 'Role', 'Nama_Pengguna'])]
#[Hidden(['Password'])]
class Pengguna extends Authenticatable implements FilamentUser, HasName
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

    public function getAuthPassword(): string
    {
        return $this->Password;
    }

    public function getAuthPasswordName(): string
    {
        return 'Password';
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->Role, [
            self::ROLE_ADMIN,
            self::ROLE_PETUGAS,
            self::ROLE_MANAJEMEN,
            self::ROLE_DOKTER,
        ], true);
    }

    public function getFilamentName(): string
    {
        return $this->Nama_Pengguna;
    }

    public function auditTrail(): HasMany
    {
        return $this->hasMany(AuditTrail::class, 'ID_Pengguna', 'ID_Pengguna');
    }
}
