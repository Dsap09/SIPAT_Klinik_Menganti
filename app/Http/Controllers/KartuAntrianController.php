<?php

namespace App\Http\Controllers;

use App\Models\Antrean;
use Illuminate\View\View;

class KartuAntrianController extends Controller
{
    public function show(string $noAntrean): View
    {
        $antrean = Antrean::query()
            ->with(['pasien', 'jadwal.poli', 'jadwal.dokter'])
            ->where('No_Antrean', $noAntrean)
            ->padaTanggal(today()->toDateString())
            ->firstOrFail();

        return view('loket.kartu', ['antrean' => $antrean]);
    }
}
