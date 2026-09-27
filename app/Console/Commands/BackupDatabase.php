<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class BackupDatabase extends Command
{
    protected $signature = 'sipat:backup
                            {--connection= : Nama koneksi basis data (default: koneksi aktif)}
                            {--keep=7 : Jumlah file backup terbaru yang disimpan}
                            {--binary= : Path ke mysqldump}';

    protected $description = 'Backup basis data SIPAT ke storage/app/backups';

    public function handle(): int
    {
        $koneksi = $this->option('connection') ?: config('database.default');

        $config = config("database.connections.{$koneksi}");

        if (($config['driver'] ?? null) !== 'mysql') {
            $this->error('Perintah ini hanya mendukung koneksi MySQL.');

            return self::FAILURE;
        }

        $folder = storage_path('app/backups');
        File::ensureDirectoryExists($folder);

        $file = $folder.'/sipat-'.now()->format('Ymd-His').'.sql';

        $hasil = Process::timeout(600)->run($this->perintahDump($config));

        if (! $hasil->successful()) {
            $this->error('Backup gagal: '.trim($hasil->errorOutput()));

            return self::FAILURE;
        }

        File::put($file, $hasil->output());

        $this->info('Backup tersimpan: '.$file);

        $this->bersihkan($folder, (int) $this->option('keep'));

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return list<string>
     */
    private function perintahDump(array $config): array
    {
        $perintah = [
            $this->option('binary') ?: ($config['dump_binary'] ?? 'mysqldump'),
            '--host='.($config['host'] ?? '127.0.0.1'),
            '--port='.($config['port'] ?? '3306'),
            '--user='.($config['username'] ?? 'root'),
            '--single-transaction',
            $config['database'],
        ];

        if (filled($config['password'] ?? null)) {
            $perintah[] = '--password='.$config['password'];
        }

        return $perintah;
    }

    private function bersihkan(string $folder, int $keep): void
    {
        if ($keep <= 0) {
            return;
        }

        $kedaluwarsa = collect(File::files($folder))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->slice($keep);

        foreach ($kedaluwarsa as $file) {
            File::delete($file->getPathname());
        }
    }
}
