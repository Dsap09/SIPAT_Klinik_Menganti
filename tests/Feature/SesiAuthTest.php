<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SesiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_kolom_user_id_pada_tabel_sessions_bertipe_string(): void
    {
        $this->assertContains(
            Schema::getColumnType('sessions', 'user_id'),
            ['string', 'varchar'],
            'sessions.user_id harus bertipe string karena ID_Pengguna berupa string seperti USR-01. '
            .'Tipe bigint bawaan Laravel membuat MySQL menolak penulisan sesi terautentikasi, '
            .'sehingga login tidak pernah tersimpan (tidak terdeteksi di SQLite).'
        );
    }
}
