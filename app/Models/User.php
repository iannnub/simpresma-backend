<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nim_nip',
        'nama',
        'email',
        'password',
        'role',
        'prodi_id',
        'no_whatsapp',
        'telegram_chat_id',
        'telegram_connected_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'              => 'hashed',
            'role'                  => 'array',
            'telegram_connected_at' => 'datetime',
        ];
    }

    // ─── Relasi ──────────────────────────────────────────

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('created_at');
    }

    public function roleHistories(): HasMany
    {
        return $this->hasMany(RoleHistory::class, 'user_id')->latest();
    }

    public function roleChangesMade(): HasMany
    {
        return $this->hasMany(RoleHistory::class, 'changed_by')->latest();
    }

    public function verifikatorProdis(): HasMany
    {
        return $this->hasMany(VerifikatorProdi::class);
    }

    public function pengajuans(): HasMany
    {
        return $this->hasMany(Pengajuan::class);
    }

    public function pengajuansVerified(): HasMany
    {
        return $this->hasMany(Pengajuan::class, 'verifikator_id');
    }

    public function pengajuansProcessed(): HasMany
    {
        return $this->hasMany(Pengajuan::class, 'tendik_id');
    }

    // ─── Helper Role Management ────────────────────────────

    /**
     * Cek apakah user memiliki suatu role.
     * Cek di JSON column `role` terlebih dahulu, lalu fallback ke relasi `roles`.
     */
    public function hasRole(string $role): bool
    {
        $roles = (array) ($this->role ?? []);
        if (in_array($role, $roles, true)) {
            return true;
        }

        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Cek apakah user memiliki setidaknya satu role dari daftar role.
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $r) {
            if ($this->hasRole($r)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Assign role ke user + simpan audit trail di role_history.
     */
    public function assignRole(string $roleName, ?int $changedBy = null, ?string $notes = null): bool
    {
        $currentRoles = (array) ($this->role ?? []);

        if (!in_array($roleName, $currentRoles, true)) {
            $currentRoles[] = $roleName;
            $this->role = array_values(array_unique($currentRoles));
            $this->save();

            // Sync ke user_roles jika Role ada di tabel roles
            $roleModel = Role::where('name', $roleName)->first();
            if ($roleModel) {
                $this->roles()->syncWithoutDetaching([$roleModel->id]);
            }

            // Catat audit trail
            RoleHistory::create([
                'user_id'    => $this->id,
                'action'     => 'assign',
                'role_name'  => $roleName,
                'changed_by' => $changedBy,
                'notes'      => $notes,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Revoke role dari user + simpan audit trail di role_history.
     *
     * @throws \InvalidArgumentException jika role terakhir atau admin terakhir
     */
    public function revokeRole(string $roleName, ?int $changedBy = null, ?string $notes = null): bool
    {
        $currentRoles = (array) ($this->role ?? []);

        if (in_array($roleName, $currentRoles, true)) {
            // Validasi: User harus punya minimal 1 role aktif
            if (count($currentRoles) <= 1) {
                throw new \InvalidArgumentException('User harus memiliki minimal 1 role aktif.');
            }

            // Validasi: Role admin tidak boleh dicabut jika merupakan admin terakhir di sistem
            if ($roleName === 'admin') {
                $otherAdminCount = User::where('id', '!=', $this->id)
                    ->whereJsonContains('role', 'admin')
                    ->count();

                if ($otherAdminCount === 0) {
                    throw new \InvalidArgumentException('Role admin tidak dapat dihapus karena ini adalah satu-satunya admin aktif di sistem.');
                }
            }

            $currentRoles = array_values(array_diff($currentRoles, [$roleName]));
            $this->role = $currentRoles;
            $this->save();

            // Detach dari user_roles
            $roleModel = Role::where('name', $roleName)->first();
            if ($roleModel) {
                $this->roles()->detach($roleModel->id);
            }

            // Catat audit trail
            RoleHistory::create([
                'user_id'    => $this->id,
                'action'     => 'revoke',
                'role_name'  => $roleName,
                'changed_by' => $changedBy,
                'notes'      => $notes,
            ]);

            return true;
        }

        return false;
    }
}
