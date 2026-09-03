<?php

namespace App\Http\Controllers\Tendik;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tendik\FinalisasiPengajuanRequest;
use App\Models\Pengajuan;
use App\Services\PengajuanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    /**
     * GET /api/tendik/pengajuan
     * List pengajuan dari semua prodi dengan filter status (diterima, riwayat/selesai, all).
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', 'diterima');

        $query = Pengajuan::with([
            'mahasiswa:id,nama,nim_nip,no_whatsapp',
            'prodi:id,kode,singkatan,nama',
            'bidang:id,nama',
            'tingkatan:id,nama,urutan',
            'tahapan:id,kode,nama,urutan',
            'verifikator:id,nama',
            'mataKuliahs:id,kode_mk,nama_mk,sks',
        ])
            ->latest('id');

        if ($status === 'riwayat' || $status === 'history' || $status === 'selesai') {
            $query->where('status', 'selesai');
        } elseif ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $pengajuan = $query->paginate(15);

        $diterimaCount = Pengajuan::where('status', 'diterima')->count();
        $selesaiCount = Pengajuan::where('status', 'selesai')->count();

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => [
                'items'  => $pengajuan->items(),
                'counts' => [
                    'diterima' => $diterimaCount,
                    'riwayat'  => $selesaiCount,
                    'total'    => $diterimaCount + $selesaiCount,
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
     * GET /api/tendik/pengajuan/{id}
     * Detail lengkap pengajuan + list MK + snapshot_huruf_nilai sebagai referensi wajib.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $pengajuan = Pengajuan::with([
            'mahasiswa:id,nama,nim_nip,email,no_whatsapp',
            'prodi:id,kode,singkatan,nama',
            'bidang:id,nama',
            'tingkatan:id,nama,urutan',
            'tahapan:id,kode,nama,urutan',
            'verifikator:id,nama',
            'pengajuanMataKuliahs.mataKuliah:id,kode_mk,nama_mk,sks',
        ])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => $pengajuan,
        ]);
    }

    /**
     * POST /api/tendik/pengajuan/{id}/finalisasi
     * Finalisasi konversi SKS: input nilai per MK (wajib sama persis dengan snapshot_huruf_nilai).
     * Delegate ke PengajuanService::finalisasi() — validasi strict sudah ada di sana.
     */
    public function finalisasi(FinalisasiPengajuanRequest $request, int $id, PengajuanService $pengajuanService): JsonResponse
    {
        $pengajuan = Pengajuan::findOrFail($id);

        // Delegate ke PengajuanService::finalisasi() yang sudah handle:
        // 1. Cek status = 'diterima'
        // 2. Cek semua MK terisi nilainya
        // 3. Validasi strict: huruf_nilai === snapshot_huruf_nilai (422 jika berbeda)
        // 4. Update huruf_nilai per baris pengajuan_mata_kuliah
        // 5. Set tendik_id, processed_at, status = 'selesai'
        $pengajuan = $pengajuanService->finalisasi($pengajuan, $request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil difinalisasi. Status berubah menjadi selesai.',
            'data'    => $pengajuan,
        ]);
    }
}
