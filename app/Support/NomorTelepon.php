<?php

namespace App\Support;

class NomorTelepon
{
    /**
     * Menyeragamkan nomor telepon ke format lokal Indonesia (08xxxxxxxxxx)
     * agar pencarian saat login selalu cocok dengan data yang tersimpan.
     */
    public static function normalisasi(string $nilai): string
    {
        $bersih = (string) preg_replace('/[^0-9+]/', '', trim($nilai));

        if (str_starts_with($bersih, '+62')) {
            return '0'.substr($bersih, 3);
        }

        if (str_starts_with($bersih, '62') && strlen($bersih) > 10) {
            return '0'.substr($bersih, 2);
        }

        return $bersih;
    }
}
