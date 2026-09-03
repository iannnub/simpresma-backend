<?php

namespace Database\Seeders;

use App\Models\TahapanLomba;
use Illuminate\Database\Seeder;

class TahapanLombaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'mendaftar',        'nama' => 'Mendaftar',        'urutan' => 1],
            ['kode' => 'lolos_tahap_awal', 'nama' => 'Lolos Tahap Awal', 'urutan' => 2],
            ['kode' => 'finalis',          'nama' => 'Finalis',           'urutan' => 3],
            ['kode' => 'pemenang',         'nama' => 'Pemenang',          'urutan' => 4],
        ];

        foreach ($data as $row) {
            TahapanLomba::firstOrCreate(['kode' => $row['kode']], $row);
        }
    }
}
