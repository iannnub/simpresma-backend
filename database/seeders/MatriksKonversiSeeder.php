<?php

namespace Database\Seeders;

use App\Models\MatriksKonversi;
use Illuminate\Database\Seeder;

class MatriksKonversiSeeder extends Seeder
{
    public function run(): void
    {
        // 24 baris: 6 tingkatan x 4 tahapan
        // NULL = kombinasi tidak valid / tidak menghasilkan konversi
        $data = [
            // Internasional (tingkatan_id=1)
            ['tingkatan_id' => 1, 'tahapan_id' => 1, 'min_sks' => null, 'max_sks' => null, 'huruf_nilai' => null],
            ['tingkatan_id' => 1, 'tahapan_id' => 2, 'min_sks' => 2,    'max_sks' => 3,    'huruf_nilai' => 'AB'],
            ['tingkatan_id' => 1, 'tahapan_id' => 3, 'min_sks' => 4,    'max_sks' => 6,    'huruf_nilai' => 'A'],
            ['tingkatan_id' => 1, 'tahapan_id' => 4, 'min_sks' => 8,    'max_sks' => 12,   'huruf_nilai' => 'A'],

            // Nasional Kementerian (tingkatan_id=2)
            ['tingkatan_id' => 2, 'tahapan_id' => 1, 'min_sks' => null, 'max_sks' => null, 'huruf_nilai' => null],
            ['tingkatan_id' => 2, 'tahapan_id' => 2, 'min_sks' => null, 'max_sks' => null, 'huruf_nilai' => null],
            ['tingkatan_id' => 2, 'tahapan_id' => 3, 'min_sks' => 4,    'max_sks' => 6,    'huruf_nilai' => 'AB'],
            ['tingkatan_id' => 2, 'tahapan_id' => 4, 'min_sks' => 6,    'max_sks' => 9,    'huruf_nilai' => 'A'],

            // Nasional Kementerian PKM (tingkatan_id=3)
            ['tingkatan_id' => 3, 'tahapan_id' => 1, 'min_sks' => null, 'max_sks' => null, 'huruf_nilai' => null],
            ['tingkatan_id' => 3, 'tahapan_id' => 2, 'min_sks' => 4,    'max_sks' => 6,    'huruf_nilai' => 'A'],
            ['tingkatan_id' => 3, 'tahapan_id' => 3, 'min_sks' => 6,    'max_sks' => 9,    'huruf_nilai' => 'A'],
            ['tingkatan_id' => 3, 'tahapan_id' => 4, 'min_sks' => 8,    'max_sks' => 12,   'huruf_nilai' => 'A'],

            // Nasional Non Kementerian (tingkatan_id=4)
            ['tingkatan_id' => 4, 'tahapan_id' => 1, 'min_sks' => null, 'max_sks' => null, 'huruf_nilai' => null],
            ['tingkatan_id' => 4, 'tahapan_id' => 2, 'min_sks' => null, 'max_sks' => null, 'huruf_nilai' => null],
            ['tingkatan_id' => 4, 'tahapan_id' => 3, 'min_sks' => 2,    'max_sks' => 3,    'huruf_nilai' => 'AB'],
            ['tingkatan_id' => 4, 'tahapan_id' => 4, 'min_sks' => 4,    'max_sks' => 6,    'huruf_nilai' => 'A'],

            // Wilayah / Regional (tingkatan_id=5)
            ['tingkatan_id' => 5, 'tahapan_id' => 1, 'min_sks' => null, 'max_sks' => null, 'huruf_nilai' => null],
            ['tingkatan_id' => 5, 'tahapan_id' => 2, 'min_sks' => null, 'max_sks' => null, 'huruf_nilai' => null],
            ['tingkatan_id' => 5, 'tahapan_id' => 3, 'min_sks' => 2,    'max_sks' => 3,    'huruf_nilai' => 'B'],
            ['tingkatan_id' => 5, 'tahapan_id' => 4, 'min_sks' => 4,    'max_sks' => 6,    'huruf_nilai' => 'AB'],

            // Promahadesa (tingkatan_id=6)
            ['tingkatan_id' => 6, 'tahapan_id' => 1, 'min_sks' => null, 'max_sks' => null, 'huruf_nilai' => null],
            ['tingkatan_id' => 6, 'tahapan_id' => 2, 'min_sks' => 2,    'max_sks' => 3,    'huruf_nilai' => 'A'],
            ['tingkatan_id' => 6, 'tahapan_id' => 3, 'min_sks' => null, 'max_sks' => null, 'huruf_nilai' => null],
            ['tingkatan_id' => 6, 'tahapan_id' => 4, 'min_sks' => null, 'max_sks' => null, 'huruf_nilai' => null],
        ];

        foreach ($data as $row) {
            MatriksKonversi::firstOrCreate(
                ['tingkatan_id' => $row['tingkatan_id'], 'tahapan_id' => $row['tahapan_id']],
                array_merge($row, ['is_active' => 1])
            );
        }
    }
}
