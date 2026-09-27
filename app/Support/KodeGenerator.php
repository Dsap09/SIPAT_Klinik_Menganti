<?php

namespace App\Support;

class KodeGenerator
{
    public static function berikutnya(string $modelClass, string $prefix, int $panjang = 2): string
    {
        $model = new $modelClass;

        $kolom = $model->getKeyName();

        $terakhir = $modelClass::query()->orderByDesc($kolom)->value($kolom);

        $urutan = $terakhir === null
            ? 1
            : ((int) substr($terakhir, -$panjang)) + 1;

        return $prefix.'-'.str_pad((string) $urutan, $panjang, '0', STR_PAD_LEFT);
    }
}
