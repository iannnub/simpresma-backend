<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan role admin tersedia di tabel roles
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['name' => 'admin', 'display_name' => 'Administrator']
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@simpresma.unej.ac.id'],
            [
                'nim_nip'     => '198501012010121001',
                'nama'        => 'Super Admin SIMPRESMA',
                'email'       => 'admin@simpresma.unej.ac.id',
                'password'    => Hash::make('admin123'),
                'role'        => ['admin'],
                'no_whatsapp' => '081234567890',
                'prodi_id'    => null,
            ]
        );

        // Pastikan role JSON memiliki 'admin'
        $currentRoles = (array) ($admin->role ?? []);
        if (!in_array('admin', $currentRoles, true)) {
            $currentRoles[] = 'admin';
            $admin->role = array_values(array_unique($currentRoles));
            $admin->save();
        }

        // Pastikan relasi pivot user_roles juga tersinkronisasi
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        $this->command->info("Admin user created/verified: {$admin->email} with role: admin");
    }
}
