<?php

use App\Http\Controllers\KartuAntrianController;
use App\Http\Controllers\PendaftaranController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PendaftaranController::class, 'beranda'])->name('beranda');
Route::get('/daftar', [PendaftaranController::class, 'form'])->name('pendaftaran.form');
Route::post('/daftar', [PendaftaranController::class, 'simpan'])
    ->middleware('throttle:pendaftaran')
    ->name('pendaftaran.simpan');

Route::get('/status/{noAntrean}', [PendaftaranController::class, 'status'])->name('pendaftaran.status');
Route::get('/status/{noAntrean}/data', [PendaftaranController::class, 'statusData'])
    ->middleware('throttle:status-antrean')
    ->name('pendaftaran.status.data');
Route::post('/status/{noAntrean}/batal', [PendaftaranController::class, 'batal'])->name('pendaftaran.batal');
Route::get('/status/{noAntrean}/jadwal-ulang', [PendaftaranController::class, 'formJadwalUlang'])->name('pendaftaran.jadwalUlang.form');
Route::post('/status/{noAntrean}/jadwal-ulang', [PendaftaranController::class, 'jadwalUlang'])->name('pendaftaran.jadwalUlang.simpan');

Route::get('/kartu/{noAntrean}', [KartuAntrianController::class, 'show'])
    ->middleware(['auth', 'role:Petugas,Admin'])
    ->name('kartu');
