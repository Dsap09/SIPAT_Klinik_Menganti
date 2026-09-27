<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class RestoreDatabase extends Command
{
    protected $signature = 'sipat:restore
                            {file : Path ke file .sql hasil backup}
                            {--connection= : Nama koneksi basis data (default: koneksi aktif)}
                            {--force : Lewati konfirmasi}
                            {--binary= : Path ke client mysql}';

    protected $description = 'Pulihkan basis data SIPAT dari file backup';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (! File::exists($file)) {
            $this->error("File tidak ditemukan: {$file}");

            return self::FAILURE;
        }

        $koneksi = $this->option('connection') ?: config('database.default');

        $config = config("database.connections.{$koneksi}");

        if (($config['driver'] ?? null) !== 'mysql') {
            $this->error('Perintah ini hanya mendukung koneksi MySQL.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Timpa basis data '{$config['database']}' dengan {$file}?")) {
            $this->info('Pemulihan dibatalkan.');

            return self::FAILURE;
        }

        $handle = fopen($file, 'r');

        $hasil = Process::timeout(600)->input($handle)->run($this->perintahClient($config));

        fclose($handle);

        if (! $hasil->successful()) {
            $this->error('Pemulihan gagal: '.trim($hasil->errorOutput()));

            return self::FAILURE;
        }

        $this->info('Pemulihan selesai dari: '.$file);

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return list<string>
     */
    private function perintahClient(array $config): array
    {
        $perintah = [
            $this->option('binary') ?: ($config['client_binary'] ?? 'mysql'),
            '--host='.($config['host'] ?? '127.0.0.1'),
            '--port='.($config['port'] ?? '3306'),
            '--user='.($config['username'] ?? 'root'),
            $config['database'],
        ];

        if (filled($config['password'] ?? null)) {
            $perintah[] = '--password='.$config['password'];
        }

        return $perintah;
    }
}
