<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RoleHistory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    /**
     * GET /api/admin/users
     * List user dengan filter (search, role, prodi_id).
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with([
            'prodi:id,kode,singkatan,nama',
            'roles:id,name,display_name',
            'verifikatorProdis' => function ($q) {
                $q->where('is_active', 1)->with('prodi:id,kode,singkatan,nama');
            },
        ]);

        // Search: nama, email, nim_nip
        if ($request->filled('search')) {
            $search = '%' . trim($request->input('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', $search)
                  ->orWhere('email', 'like', $search)
                  ->orWhere('nim_nip', 'like', $search);
            });
        }

        // Filter role
        if ($request->filled('role')) {
            $role = trim($request->input('role'));
            $query->whereJsonContains('role', $role);
        }

        // Filter prodi
        if ($request->filled('prodi_id')) {
            $query->where('prodi_id', (int) $request->input('prodi_id'));
        }

        $users = $query->orderBy('nama', 'asc')->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => [
                'items' => $users->items(),
                'meta'  => [
                    'current_page' => $users->currentPage(),
                    'last_page'    => $users->lastPage(),
                    'per_page'     => $users->perPage(),
                    'total'        => $users->total(),
                ],
            ],
        ]);
    }

    /**
     * POST /api/admin/users/{id}/roles
     * Assign role baru ke user (mendukung prodi_id jika role = verifikator).
     */
    public function assignRole(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role_name' => ['required', 'string', 'in:admin,wadek,tendik,verifikator,dosen,mahasiswa'],
            'prodi_id'  => ['nullable', 'integer', 'exists:prodi,id'],
            'notes'     => ['nullable', 'string', 'max:500'],
        ], [
            'role_name.required' => 'Nama role wajib dipilih.',
            'role_name.in'       => 'Role yang dipilih tidak valid.',
            'prodi_id.exists'    => 'Program studi yang dipilih tidak valid.',
        ]);

        $currentRoles = (array) ($user->role ?? []);
        if (in_array($validated['role_name'], $currentRoles, true)) {
            // Jika role verifikator sudah ada, tapi ingin tambah prodi verifikasi baru
            if ($validated['role_name'] === 'verifikator' && !empty($validated['prodi_id'])) {
                $prodi = \App\Models\Prodi::find($validated['prodi_id']);
                \App\Models\VerifikatorProdi::updateOrCreate(
                    ['user_id' => $user->id, 'prodi_id' => $validated['prodi_id']],
                    ['assigned_by' => $request->user()->id, 'is_active' => 1]
                );

                \App\Models\RoleHistory::create([
                    'user_id'    => $user->id,
                    'action'     => 'assign',
                    'role_name'  => 'verifikator',
                    'changed_by' => $request->user()->id,
                    'notes'      => $validated['notes'] ?? "Penugasan Verifikator Prodi {$prodi->singkatan}",
                ]);

                $user->load(['prodi', 'roles', 'verifikatorProdis.prodi']);

                return response()->json([
                    'success' => true,
                    'message' => "Prodi verifikasi '{$prodi->singkatan}' berhasil ditambahkan ke user {$user->nama}.",
                    'data'    => $user,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "User {$user->nama} sudah memiliki role '{$validated['role_name']}'.",
            ], 422);
        }

        $user->assignRole(
            $validated['role_name'],
            $request->user()->id,
            $validated['notes'] ?? null
        );

        // Jika role verifikator dan memilih prodi_id (misal SI, TI, IF)
        if ($validated['role_name'] === 'verifikator' && !empty($validated['prodi_id'])) {
            $prodi = \App\Models\Prodi::find($validated['prodi_id']);
            \App\Models\VerifikatorProdi::updateOrCreate(
                ['user_id' => $user->id, 'prodi_id' => $validated['prodi_id']],
                ['assigned_by' => $request->user()->id, 'is_active' => 1]
            );

            // Perbarui catatan history jika ada
            if ($prodi) {
                \App\Models\RoleHistory::where('user_id', $user->id)
                    ->where('role_name', 'verifikator')
                    ->latest()
                    ->first()
                    ?->update([
                        'notes' => $validated['notes'] ?? "Penugasan Dosen Verifikator Prodi {$prodi->singkatan}",
                    ]);
            }
        }

        $user->load(['prodi', 'roles', 'verifikatorProdis.prodi']);

        return response()->json([
            'success' => true,
            'message' => "Role '{$validated['role_name']}' berhasil ditambahkan ke user {$user->nama}.",
            'data'    => $user,
        ]);
    }

    /**
     * DELETE /api/admin/users/{id}/roles/{roleName}
     * Hapus/revoke role dari user.
     */
    public function revokeRole(Request $request, int $id, string $roleName): JsonResponse
    {
        $user = User::findOrFail($id);

        $notes = $request->input('notes');

        try {
            $user->revokeRole(
                $roleName,
                $request->user()->id,
                $notes
            );

            // Jika mencabut role verifikator, nonaktifkan juga semua verifikator_prodi
            if ($roleName === 'verifikator') {
                \App\Models\VerifikatorProdi::where('user_id', $user->id)->update(['is_active' => 0]);
            }
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $user->load(['prodi', 'roles', 'verifikatorProdis.prodi']);

        return response()->json([
            'success' => true,
            'message' => "Role '{$roleName}' berhasil dicabut dari user {$user->nama}.",
            'data'    => $user,
        ]);
    }

    /**
     * POST /api/admin/users/{id}/verifikator-prodi
     * Assign / ubah scope verifikator prodi (SI, TI, IF).
     */
    public function assignVerifikatorProdi(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'prodi_id' => ['required', 'integer', 'exists:prodi,id'],
            'notes'    => ['nullable', 'string', 'max:500'],
        ], [
            'prodi_id.required' => 'Pilih program studi verifikator.',
            'prodi_id.exists'   => 'Program studi tidak valid.',
        ]);

        $prodi = \App\Models\Prodi::findOrFail($validated['prodi_id']);

        // Pastikan user memiliki role verifikator
        if (!$user->hasRole('verifikator')) {
            $user->assignRole('verifikator', $request->user()->id, 'Auto-assign role verifikator');
        }

        \App\Models\VerifikatorProdi::updateOrCreate(
            ['user_id' => $user->id, 'prodi_id' => $validated['prodi_id']],
            ['assigned_by' => $request->user()->id, 'is_active' => 1]
        );

        \App\Models\RoleHistory::create([
            'user_id'    => $user->id,
            'action'     => 'assign',
            'role_name'  => 'verifikator',
            'changed_by' => $request->user()->id,
            'notes'      => $validated['notes'] ?? "Penugasan Verifikator Prodi {$prodi->singkatan} oleh Admin",
        ]);

        $user->load(['prodi', 'roles', 'verifikatorProdis.prodi']);

        return response()->json([
            'success' => true,
            'message' => "User {$user->nama} berhasil ditugaskan sebagai Verifikator {$prodi->singkatan}.",
            'data'    => $user,
        ]);
    }

    /**
     * DELETE /api/admin/users/{id}/verifikator-prodi/{prodiId}
     * Cabut scope prodi dari verifikator.
     */
    public function revokeVerifikatorProdi(Request $request, int $id, int $prodiId): JsonResponse
    {
        $user = User::findOrFail($id);
        $prodi = \App\Models\Prodi::findOrFail($prodiId);

        \App\Models\VerifikatorProdi::where('user_id', $user->id)
            ->where('prodi_id', $prodiId)
            ->update(['is_active' => 0]);

        $activeScopes = \App\Models\VerifikatorProdi::where('user_id', $user->id)
            ->where('is_active', 1)
            ->count();

        // Jika tidak memiliki scope aktif lagi, catat audit trail
        \App\Models\RoleHistory::create([
            'user_id'    => $user->id,
            'action'     => 'revoke',
            'role_name'  => 'verifikator',
            'changed_by' => $request->user()->id,
            'notes'      => "Pencabutan lingkup Verifikator Prodi {$prodi->singkatan}" . ($activeScopes === 0 ? " (sudah tidak memiliki prodi aktif)" : ""),
        ]);

        $user->load(['prodi', 'roles', 'verifikatorProdis.prodi']);

        return response()->json([
            'success' => true,
            'message' => "Lingkup Verifikator Prodi {$prodi->singkatan} berhasil dicabut dari {$user->nama}.",
            'data'    => $user,
        ]);
    }

    /**
     * GET /api/admin/role-history
     * Get audit trail perubahan role (opsional filter by user_id).
     */
    public function roleHistory(Request $request): JsonResponse
    {
        $query = RoleHistory::with([
            'user:id,nama,email,nim_nip',
            'changedBy:id,nama,email',
        ]);

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        $history = $query->latest('created_at')->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => [
                'items' => $history->items(),
                'meta'  => [
                    'current_page' => $history->currentPage(),
                    'last_page'    => $history->lastPage(),
                    'per_page'     => $history->perPage(),
                    'total'        => $history->total(),
                ],
            ],
        ]);
    }

    /**
     * GET /api/admin/available-roles
     * Daftar role yang dapat di-assign.
     */
    public function availableRoles(): JsonResponse
    {
        $roles = Role::orderBy('id', 'asc')->get(['id', 'name', 'display_name']);

        return response()->json([
            'success' => true,
            'data'    => $roles,
        ]);
    }
}
