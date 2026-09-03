<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Master data tanpa dependensi
            ProdiSeeder::class,
            RoleSeeder::class,
            TingkatanLombaSeeder::class,
            TahapanLombaSeeder::class,

            // 2. Matriks dan bidang (butuh tingkatan + tahapan)
            MatriksKonversiSeeder::class,
            BidangLombaSeeder::class,

            // 3. MK (butuh prodi)
            MataKuliahSeeder::class,

            // 4. Mapping bidang -> MK (butuh bidang + MK)
            BidangMataKuliahSeeder::class,

            // 5. Dummy users + roles + verifikator_prodi (butuh prodi + role)
            DummyUserSeeder::class,
        ]);
    }
}
