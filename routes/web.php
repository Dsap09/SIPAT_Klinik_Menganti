<?php

use App\Http\Controllers\KartuAntrianController;
use App\Http\Controllers\Pasien\AkunPasienController;
use App\Http\Controllers\Pasien\DashboardPasienController;
use App\Http\Controllers\Pasien\PendaftaranPasienController;
use App\Http\Controllers\PendaftaranController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PendaftaranController::class, 'beranda'])->name('beranda');
Route::get('/cek', [PendaftaranController::class, 'cekStatus'])->name('pendaftaran.cek');
Route::get('/daftar', [PendaftaranController::class, 'form'])->name('pendaftaran.form');

Route::get('/status/{noAntrean}', [PendaftaranController::class, 'status'])->name('pendaftaran.status');
Route::get('/status/{noAntrean}/data', [PendaftaranController::class, 'statusData'])
    ->middleware('throttle:status-antrean')
    ->name('pendaftaran.status.data');
Route::post('/status/{noAntrean}/batal', [PendaftaranController::class, 'batal'])->name('pendaftaran.batal');
Route::get('/status/{noAntrean}/jadwal-ulang', [PendaftaranController::class, 'formJadwalUlang'])->name('pendaftaran.jadwalUlang.form');
Route::post('/status/{noAntrean}/jadwal-ulang', [PendaftaranController::class, 'jadwalUlang'])->name('pendaftaran.jadwalUlang.simpan');

Route::prefix('pasien')->name('pasien.')->group(function () {
    Route::get('/register', [AkunPasienController::class, 'formRegister'])->name('register');
    Route::post('/register', [AkunPasienController::class, 'simpanRegister'])
        ->middleware('throttle:pendaftaran')
        ->name('register.simpan');

    Route::get('/login', [AkunPasienController::class, 'formLogin'])->name('login');
    Route::post('/login', [AkunPasienController::class, 'masuk'])
        ->middleware('throttle:pasien-login')
        ->name('login.proses');

    Route::middleware('auth:pasien')->group(function () {
        Route::post('/logout', [AkunPasienController::class, 'keluar'])->name('logout');

        Route::get('/dashboard', [DashboardPasienController::class, 'dashboard'])->name('dashboard');
        Route::get('/riwayat', [DashboardPasienController::class, 'riwayat'])->name('riwayat');
        Route::get('/profil', [DashboardPasienController::class, 'profil'])->name('profil');
        Route::put('/profil', [DashboardPasienController::class, 'simpanProfil'])->name('profil.simpan');

        Route::get('/daftar', [PendaftaranPasienController::class, 'form'])->name('daftar');
        Route::post('/daftar', [PendaftaranPasienController::class, 'simpan'])->name('daftar.simpan');
    });
});

Route::get('/kartu/{noAntrean}', [KartuAntrianController::class, 'show'])
    ->middleware(['auth', 'role:Petugas,Admin'])
    ->name('kartu');
