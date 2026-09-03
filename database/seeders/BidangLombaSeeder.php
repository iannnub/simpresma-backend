<?php

namespace Database\Seeders;

use App\Models\BidangLomba;
use Illuminate\Database\Seeder;

class BidangLombaSeeder extends Seeder
{
    public function run(): void
    {
        $bidang = [
            'Kewirausahaan',
            'Graphic Design',
            'Desain Poster',
            'VGK',
            'UI/UX',
            'Programming',
            'Software Development',
            'Karya Tulis Ilmiah',
            'Matematika Komputasi',
            'Non Akademik',
            'Immersive Development',
            'KKN',
            'Embedded dan IOT',
            'Jaringan dan Sekuritas',
            'PPK Ormawa',
            'BMC',
            'Data Science',
            'Data Analytics',
        ];

        foreach ($bidang as $nama) {
            BidangLomba::firstOrCreate(['nama' => $nama], ['nama' => $nama, 'is_active' => 1]);
        }
    }
}
