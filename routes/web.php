<?php

use App\Http\Controllers\PendaftaranController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PendaftaranController::class, 'beranda'])->name('beranda');
Route::get('/daftar', [PendaftaranController::class, 'form'])->name('pendaftaran.form');
Route::post('/daftar', [PendaftaranController::class, 'simpan'])->name('pendaftaran.simpan');
Route::get('/status/{noAntrean}', [PendaftaranController::class, 'status'])->name('pendaftaran.status');
