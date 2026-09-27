<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class BackupTest extends TestCase
{
    private string $folder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->folder = storage_path('app/backups');
        File::deleteDirectory($this->folder);

        config()->set('database.connections.uji_backup', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'sipat_klinik_menganti',
            'username' => 'root',
            'password' => '',
            'dump_binary' => 'mysqldump',
            'client_binary' => 'mysql',
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->folder);

        parent::tearDown();
    }

    public function test_backup_menyimpan_file_sql(): void
    {
        Process::fake(['*' => Process::result("-- dump sipat\n")]);

        $this->artisan('sipat:backup', ['--connection' => 'uji_backup'])->assertSuccessful();

        $file = collect(File::files($this->folder))->first();

        $this->assertNotNull($file);
        $this->assertStringContainsString('-- dump sipat', File::get($file->getPathname()));

        Process::assertRan(fn ($process) => str_contains(implode(' ', $process->command), 'mysqldump'));
    }

    public function test_backup_membersihkan_file_lama(): void
    {
        Process::fake(['*' => Process::result("-- dump sipat\n")]);

        File::ensureDirectoryExists($this->folder);

        foreach ([3, 2, 1] as $hari) {
            $file = $this->folder."/sipat-lama-{$hari}.sql";
            File::put($file, 'lama');
            touch($file, now()->subDays($hari)->timestamp);
        }

        $this->artisan('sipat:backup', [
            '--connection' => 'uji_backup',
            '--keep' => 2,
        ])->assertSuccessful();

        $this->assertCount(2, File::files($this->folder));
    }

    public function test_backup_gagal_jika_proses_dump_error(): void
    {
        Process::fake(['*' => Process::result('', 'mysqldump: tidak ditemukan', 1)]);

        $this->artisan('sipat:backup', ['--connection' => 'uji_backup'])->assertFailed();

        $this->assertSame([], File::files($this->folder));
    }

    public function test_restore_gagal_jika_file_backup_tidak_ada(): void
    {
        $this->artisan('sipat:restore', [
            'file' => $this->folder.'/tidak-ada.sql',
            '--connection' => 'uji_backup',
            '--force' => true,
        ])->assertFailed();
    }

    public function test_restore_mengirim_backup_ke_client_mysql(): void
    {
        Process::fake(['*' => Process::result('')]);

        File::ensureDirectoryExists($this->folder);

        $file = $this->folder.'/uji.sql';
        File::put($file, "-- isi backup\n");

        $this->artisan('sipat:restore', [
            'file' => $file,
            '--connection' => 'uji_backup',
            '--force' => true,
        ])->assertSuccessful();

        Process::assertRan(fn ($process) => str_contains(implode(' ', $process->command), 'mysql'));
    }
}
