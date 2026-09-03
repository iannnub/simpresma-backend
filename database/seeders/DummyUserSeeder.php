<?php

namespace Database\Seeders;

use App\Models\Prodi;
use App\Models\Role;
use App\Models\User;
use App\Models\VerifikatorProdi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyUserSeeder extends Seeder
{
    public function run(): void
    {
        $prodiSI = Prodi::where('singkatan', 'SI')->first();
        $prodiTI = Prodi::where('singkatan', 'TI')->first();
        $prodiIF = Prodi::where('singkatan', 'IF')->first();

        $roleMhs   = Role::where('name', 'mahasiswa')->first();
        $roleVerif = Role::where('name', 'verifikator')->first();
        $roleTendik = Role::where('name', 'tendik')->first();
        $roleWadek  = Role::where('name', 'wadek')->first();

        // ─── Wadek (dibuat dulu karena jadi assigned_by di verifikator) ────
        $wadek = User::firstOrCreate(
            ['email' => 'wadek@test.com'],
            ['nim_nip' => 'NIP.99001', 'nama' => 'Dr. Wadek', 'password' => Hash::make('password'), 'prodi_id' => null, 'no_whatsapp' => '08199990001']
        );
        $wadek->roles()->syncWithoutDetaching([$roleWadek->id]);

        // ─── Mahasiswa ─────────────────────────────────────────────────────
        $mhsSI = User::firstOrCreate(
            ['email' => 'mhs.si@test.com'],
            ['nim_nip' => '220210101001', 'nama' => 'Mahasiswa SI', 'password' => Hash::make('password'), 'prodi_id' => $prodiSI->id, 'no_whatsapp' => '08111110001']
        );
        $mhsSI->roles()->syncWithoutDetaching([$roleMhs->id]);

        $mhsTI = User::firstOrCreate(
            ['email' => 'mhs.ti@test.com'],
            ['nim_nip' => '220210201001', 'nama' => 'Mahasiswa TI', 'password' => Hash::make('password'), 'prodi_id' => $prodiTI->id, 'no_whatsapp' => '08111110002']
        );
        $mhsTI->roles()->syncWithoutDetaching([$roleMhs->id]);

        $mhsIF = User::firstOrCreate(
            ['email' => 'mhs.if@test.com'],
            ['nim_nip' => '220210301001', 'nama' => 'Mahasiswa IF', 'password' => Hash::make('password'), 'prodi_id' => $prodiIF->id, 'no_whatsapp' => '08111110003']
        );
        $mhsIF->roles()->syncWithoutDetaching([$roleMhs->id]);

        // ─── Verifikator ───────────────────────────────────────────────────
        $verifSI = User::firstOrCreate(
            ['email' => 'verif.si@test.com'],
            ['nim_nip' => 'NIP.10001', 'nama' => 'Verifikator SI', 'password' => Hash::make('password'), 'prodi_id' => null, 'no_whatsapp' => '08122220001']
        );
        $verifSI->roles()->syncWithoutDetaching([$roleVerif->id]);
        VerifikatorProdi::firstOrCreate(
            ['user_id' => $verifSI->id, 'prodi_id' => $prodiSI->id],
            ['assigned_by' => $wadek->id, 'is_active' => 1]
        );

        $verifTI = User::firstOrCreate(
            ['email' => 'verif.ti@test.com'],
            ['nim_nip' => 'NIP.10002', 'nama' => 'Verifikator TI', 'password' => Hash::make('password'), 'prodi_id' => null, 'no_whatsapp' => '08122220002']
        );
        $verifTI->roles()->syncWithoutDetaching([$roleVerif->id]);
        VerifikatorProdi::firstOrCreate(
            ['user_id' => $verifTI->id, 'prodi_id' => $prodiTI->id],
            ['assigned_by' => $wadek->id, 'is_active' => 1]
        );

        $verifIF = User::firstOrCreate(
            ['email' => 'verif.if@test.com'],
            ['nim_nip' => 'NIP.10003', 'nama' => 'Verifikator IF', 'password' => Hash::make('password'), 'prodi_id' => null, 'no_whatsapp' => '08122220003']
        );
        $verifIF->roles()->syncWithoutDetaching([$roleVerif->id]);
        VerifikatorProdi::firstOrCreate(
            ['user_id' => $verifIF->id, 'prodi_id' => $prodiIF->id],
            ['assigned_by' => $wadek->id, 'is_active' => 1]
        );

        // ─── Tendik ────────────────────────────────────────────────────────
        $tendik = User::firstOrCreate(
            ['email' => 'tendik@test.com'],
            ['nim_nip' => 'NIP.20001', 'nama' => 'Staff Tendik', 'password' => Hash::make('password'), 'prodi_id' => null, 'no_whatsapp' => '08133330001']
        );
        $tendik->roles()->syncWithoutDetaching([$roleTendik->id]);

        // ─── Multi-role (Verifikator SI + Tendik) ─────────────────────────
        $multi = User::firstOrCreate(
            ['email' => 'multi@test.com'],
            ['nim_nip' => 'NIP.30001', 'nama' => 'Multi Role User', 'password' => Hash::make('password'), 'prodi_id' => null, 'no_whatsapp' => '08144440001']
        );
        $multi->roles()->syncWithoutDetaching([$roleVerif->id, $roleTendik->id]);
        VerifikatorProdi::firstOrCreate(
            ['user_id' => $multi->id, 'prodi_id' => $prodiSI->id],
            ['assigned_by' => $wadek->id, 'is_active' => 1]
        );
    }
}
