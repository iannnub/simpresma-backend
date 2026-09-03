<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->json('role')->nullable()->after('email')
                    ->comment('Array role aktif user: ["mahasiswa"], ["verifikator","dosen"], dll');
            }
        });

        // Migrate existing user roles from user_roles pivot to users.role JSON column
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $roleNames = DB::table('user_roles')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('user_roles.user_id', $user->id)
                ->pluck('roles.name')
                ->toArray();

            if (empty($roleNames)) {
                $roleNames = ['mahasiswa'];
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update(['role' => json_encode(array_values(array_unique($roleNames)))]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
