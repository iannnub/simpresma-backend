<?php

namespace Database\Seeders;

use App\Models\Prodi;
use Illuminate\Database\Seeder;

class ProdiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'PSSI', 'singkatan' => 'SI', 'nama' => 'Sistem Informasi'],
            ['kode' => 'PSTI', 'singkatan' => 'TI', 'nama' => 'Teknologi Informasi'],
            ['kode' => 'PSIF', 'singkatan' => 'IF', 'nama' => 'Informatika'],
        ];

        foreach ($data as $row) {
            Prodi::firstOrCreate(['kode' => $row['kode']], $row);
        }
    }
}
