<?php

namespace Database\Seeders;

use App\Models\TingkatanLomba;
use Illuminate\Database\Seeder;

class TingkatanLombaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['urutan' => 1, 'nama' => 'Internasional'],
            ['urutan' => 2, 'nama' => 'Nasional Kementerian: Gemastik, LIDM, Satria Data, NUDC, KDMI, MTQMN'],
            ['urutan' => 3, 'nama' => 'Nasional Kementerian: PKM, P2MW, PPK Ormawa, Pilmapres, Peksiminas'],
            ['urutan' => 4, 'nama' => 'Nasional Non Kementerian / Mandiri'],
            ['urutan' => 5, 'nama' => 'Wilayah / Regional / Provinsi'],
            ['urutan' => 6, 'nama' => 'Promahadesa'],
        ];

        foreach ($data as $row) {
            TingkatanLomba::firstOrCreate(['urutan' => $row['urutan']], $row);
        }
    }
}
