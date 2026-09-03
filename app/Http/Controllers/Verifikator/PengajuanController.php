<?php

namespace App\Http\Controllers\Verifikator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Verifikator\ProcessPengajuanRequest;
use App\Models\Pengajuan;
use App\Models\VerifikatorProdi;
use App\Services\PengajuanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    /**
     * GET /api/verifikator/pengajuan
     * List pengajuan dari prodi scope verifikator dengan filter status (pending, riwayat, all).
     */
    public function index(Request $request): JsonResponse
    {
        // Ambil daftar prodi_id yang menjadi scope verifikator yang login
        $prodiIds = VerifikatorProdi::where('user_id', $request->user()->id)
            ->where('is_active', 1)
            ->pluck('prodi_id');

        if ($prodiIds->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Anda belum memiliki scope prodi verifikator yang aktif.',
                'data'    => [
                    'items' => [],
                    'counts' => [
                        'pending' => 0,
                        'riwayat' => 0,
                        'total'   => 0,
                    ],
                    'meta'  => [
                        'current_page' => 1,
                        'last_page'    => 1,
                        'per_page'     => 15,
                        'total'        => 0,
                    ],
                ],
            ]);
        }

        $status = $request->query('status', 'pending');

        $query = Pengajuan::with([
            'mahasiswa:id,nama,nim_nip,no_whatsapp',
            'prodi:id,kode,singkatan,nama',
            'bidang:id,nama',
            'tingkatan:id,nama,urutan',
            'tahapan:id,kode,nama,urutan',
            'mataKuliahs:id,kode_mk,nama_mk,sks',
            'verifikator:id,nama',
        ])
            ->whereIn('prodi_id', $prodiIds)
            ->latest('id');

        if ($status === 'riwayat' || $status === 'history') {
            $query->whereIn('status', ['diterima', 'ditolak', 'selesai']);
        } elseif ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $pengajuan = $query->paginate(15);

        $pendingCount = Pengajuan::whereIn('prodi_id', $prodiIds)->where('status', 'pending')->count();
        $riwayatCount = Pengajuan::whereIn('prodi_id', $prodiIds)->whereIn('status', ['diterima', 'ditolak', 'selesai'])->count();

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => [
                'items'  => $pengajuan->items(),
                'counts' => [
                    'pending' => $pendingCount,
                    'riwayat' => $riwayatCount,
                    'total'   => $pendingCount + $riwayatCount,
                ],
                'meta'   => [
                    'current_page' => $pengajuan->currentPage(),
                    'last_page'    => $pengajuan->lastPage(),
                    'per_page'     => $pengajuan->perPage(),
                    'total'        => $pengajuan->total(),
                ],
            ],
        ]);
    }

    /**
     * GET /api/verifikator/pengajuan/{id}
     * Detail 1 pengajuan (dengan scope check prodi verifikator).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $pengajuan = Pengajuan::with([
            'mahasiswa:id,nama,nim_nip,email,no_whatsapp',
            'prodi:id,kode,singkatan,nama',
            'bidang:id,nama',
            'tingkatan:id,nama,urutan',
            'tahapan:id,kode,nama,urutan',
            'pengajuanMataKuliahs.mataKuliah:id,kode_mk,nama_mk,sks',
            'verifikator:id,nama',
        ])
            ->findOrFail($id);

        // Scope check: verifikator hanya bisa lihat pengajuan dari prodinya
        $isInScope = VerifikatorProdi::where('user_id', $request->user()->id)
            ->where('prodi_id', $pengajuan->prodi_id)
            ->where('is_active', 1)
            ->exists();

        if (!$isInScope) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Pengajuan ini berada di luar scope prodi Anda.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => $pengajuan,
        ]);
    }

    /**
     * POST /api/verifikator/pengajuan/{id}/terima
     * Terima pengajuan: pending → diterima.
     */
    public function terima(Request $request, int $id, PengajuanService $pengajuanService): JsonResponse
    {
        $pengajuan = Pengajuan::findOrFail($id);

        $feedback = $request->input('feedback_verifikator');

        // Delegate ke PengajuanService::terima() — sudah handle scope check & status validation
        $pengajuan = $pengajuanService->terima($pengajuan, $request->user(), $feedback);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil diterima.',
            'data'    => $pengajuan,
        ]);
    }

    /**
     * POST /api/verifikator/pengajuan/{id}/tolak
     * Tolak pengajuan: pending → ditolak (feedback wajib).
     */
    public function tolak(ProcessPengajuanRequest $request, int $id, PengajuanService $pengajuanService): JsonResponse
    {
        $pengajuan = Pengajuan::findOrFail($id);

        $feedback = $request->validated()['feedback_verifikator'];

        // Delegate ke PengajuanService::tolak() — sudah handle scope check, status & feedback validation
        $pengajuan = $pengajuanService->tolak($pengajuan, $request->user(), $feedback);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil ditolak.',
            'data'    => $pengajuan,
        ]);
    }
}
