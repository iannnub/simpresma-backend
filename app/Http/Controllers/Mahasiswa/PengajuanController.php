<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Events\PengajuanSubmitted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mahasiswa\StorePengajuanRequest;
use App\Models\Pengajuan;
use App\Services\PengajuanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    /**
     * GET /api/mahasiswa/pengajuan
     * List pengajuan milik mahasiswa yang sedang login (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $pengajuan = Pengajuan::with([
            'bidang:id,nama',
            'tingkatan:id,nama,urutan',
            'tahapan:id,kode,nama,urutan',
            'mataKuliahs:id,kode_mk,nama_mk,sks',
        ])
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => [
                'items' => $pengajuan->items(),
                'meta'  => [
                    'current_page' => $pengajuan->currentPage(),
                    'last_page'    => $pengajuan->lastPage(),
                    'per_page'     => $pengajuan->perPage(),
                    'total'        => $pengajuan->total(),
                ],
            ],
        ]);
    }

    /**
     * POST /api/mahasiswa/pengajuan
     * Submit pengajuan prestasi baru.
     */
    public function store(StorePengajuanRequest $request, PengajuanService $pengajuanService): JsonResponse
    {
        $pengajuan = $pengajuanService->submit($request->validated(), $request->user());

        // Kirim notifikasi ke verifikator prodi terkait (async via queue)
        event(new PengajuanSubmitted($pengajuan));

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan prestasi berhasil disubmit.',
            'data'    => $pengajuan,
        ], 201);
    }

    /**
     * GET /api/mahasiswa/pengajuan/{id}
     * Detail 1 pengajuan milik sendiri (termasuk hasil konversi/nilai jika selesai).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $pengajuan = Pengajuan::with([
            'bidang:id,nama',
            'tingkatan:id,nama,urutan',
            'tahapan:id,kode,nama,urutan',
            'prodi:id,kode,singkatan,nama',
            'pengajuanMataKuliahs.mataKuliah:id,kode_mk,nama_mk,sks',
            'verifikator:id,nama',
            'tendik:id,nama',
        ])
            ->where('id', $id)
            ->firstOrFail();

        if ($pengajuan->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak berhak melihat pengajuan ini.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => $pengajuan,
        ]);
    }
}
