<?php

namespace App\Http\Controllers\Wadek;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wadek\AssignVerifikatorRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\VerifikatorProdi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifikatorController extends Controller
{
    /**
     * GET /api/wadek/verifikator
     * List semua verifikator aktif per prodi (join users + prodi).
     */
    public function index(Request $request): JsonResponse
    {
        $verifikators = VerifikatorProdi::with([
            'user:id,nim_nip,nama,email,no_whatsapp',
            'prodi:id,kode,singkatan,nama',
            'assignedBy:id,nama',
        ])
            ->where('is_active', 1)
            ->orderBy('prodi_id')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => $verifikators,
        ]);
    }

    /**
     * POST /api/wadek/verifikator
     * Assign dosen/user ke prodi sebagai verifikator.
     * - Jika sudah pernah ada (is_active=0), reaktivasi.
     * - Jika belum punya role 'verifikator', otomatis tambahkan.
     */
    public function store(AssignVerifikatorRequest $request): JsonResponse
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data, $request) {
            // Upsert: jika record sudah ada (unique user+prodi) → reaktivasi, else insert baru
            $verifikatorProdi = VerifikatorProdi::updateOrCreate(
                [
                    'user_id'  => $data['user_id'],
                    'prodi_id' => $data['prodi_id'],
                ],
                [
                    'assigned_by' => $request->user()->id,
                    'is_active'   => 1,
                ]
            );

            // Auto-tambah role 'verifikator' jika user belum punya
            $user        = User::findOrFail($data['user_id']);
            $roleVerif   = Role::where('name', 'verifikator')->first();
            $user->roles()->syncWithoutDetaching([$roleVerif->id]);

            return response()->json([
                'success' => true,
                'message' => 'Verifikator berhasil di-assign ke program studi.',
                'data'    => $verifikatorProdi->load(['user:id,nim_nip,nama,email', 'prodi:id,kode,singkatan,nama']),
            ], 201);
        });
    }

    /**
     * DELETE /api/wadek/verifikator/{id}
     * Cabut verifikator dari prodi (set is_active = 0).
     * Jika tidak ada prodi aktif lain → cabut role 'verifikator'.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $verifikatorProdi = VerifikatorProdi::findOrFail($id);

        return DB::transaction(function () use ($verifikatorProdi) {
            $userId = $verifikatorProdi->user_id;

            // Cabut dari prodi ini
            $verifikatorProdi->update(['is_active' => 0]);

            // Cek apakah masih ada prodi aktif lain
            $sisaProdiAktif = VerifikatorProdi::where('user_id', $userId)
                ->where('is_active', 1)
                ->count();

            // Jika tidak ada prodi aktif lain, cabut role 'verifikator'
            if ($sisaProdiAktif === 0) {
                $user      = User::find($userId);
                $roleVerif = Role::where('name', 'verifikator')->first();
                $user->roles()->detach($roleVerif->id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Verifikator berhasil dicabut dari program studi.',
                'data'    => null,
            ]);
        });
    }
}
