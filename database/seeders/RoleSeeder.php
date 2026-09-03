<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'admin',       'display_name' => 'Administrator'],
            ['name' => 'wadek',       'display_name' => 'Wakil Dekan'],
            ['name' => 'tendik',      'display_name' => 'Tenaga Kependidikan'],
            ['name' => 'verifikator', 'display_name' => 'Tim Verifikator'],
            ['name' => 'dosen',       'display_name' => 'Dosen'],
            ['name' => 'mahasiswa',   'display_name' => 'Mahasiswa'],
        ];

        foreach ($data as $row) {
            Role::firstOrCreate(['name' => $row['name']], $row);
        }
    }
}
