<?php

namespace App\Services;

use App\Models\MatriksKonversi;

class MatriksService
{
    /**
     * Lookup snapshot matriks konversi berdasarkan tingkatan dan tahapan.
     * Mengembalikan MatriksKonversi aktif atau null jika kombinasi tidak menghasilkan konversi.
     */
    public function snapshot(int $tingkatanId, int $tahapanId): ?MatriksKonversi
    {
        return MatriksKonversi::where('tingkatan_id', $tingkatanId)
            ->where('tahapan_id', $tahapanId)
            ->where('is_active', true)
            ->first();
    }
}
