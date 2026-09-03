<?php

namespace App\Http\Controllers;

use App\Models\BidangLomba;
use App\Models\BidangMataKuliah;
use App\Models\MataKuliah;
use App\Models\MatriksKonversi;
use App\Models\Prodi;
use App\Models\TahapanLomba;
use App\Models\TingkatanLomba;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefController extends Controller
{
    /**
     * GET /api/ref/prodi
     */
    public function prodi(): JsonResponse
    {
        $data = Prodi::select('id', 'kode', 'singkatan', 'nama')->get();

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/ref/tingkatan
     */
    public function tingkatan(): JsonResponse
    {
        $data = TingkatanLomba::orderBy('urutan')->get(['id', 'nama', 'urutan']);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/ref/tahapan
     */
    public function tahapan(): JsonResponse
    {
        $data = TahapanLomba::orderBy('urutan')->get(['id', 'kode', 'nama', 'urutan']);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/ref/bidang
     */
    public function bidang(): JsonResponse
    {
        $data = BidangLomba::where('is_active', 1)->get(['id', 'nama', 'keterangan']);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/ref/matriks?tingkatan_id={}&tahapan_id={}
     */
    public function matriks(Request $request): JsonResponse
    {
        $request->validate([
            'tingkatan_id' => 'required|integer|exists:tingkatan_lomba,id',
            'tahapan_id'   => 'required|integer|exists:tahapan_lomba,id',
        ]);

        $matriks = MatriksKonversi::where('tingkatan_id', $request->tingkatan_id)
            ->where('tahapan_id', $request->tahapan_id)
            ->where('is_active', 1)
            ->first(['id', 'tingkatan_id', 'tahapan_id', 'min_sks', 'max_sks', 'huruf_nilai']);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => $matriks,
        ]);
    }

    /**
     * GET /api/ref/mata-kuliah?bidang_id={}&prodi_id={}
     */
    public function mataKuliah(Request $request): JsonResponse
    {
        $request->validate([
            'bidang_id' => 'required|integer|exists:bidang_lomba,id',
            'prodi_id'  => 'required|integer|exists:prodi,id',
        ]);

        $mkIds = BidangMataKuliah::where('bidang_id', $request->bidang_id)
            ->where('is_active', 1)
            ->pluck('mata_kuliah_id');

        $data = MataKuliah::whereIn('id', $mkIds)
            ->where('prodi_id', $request->prodi_id)
            ->where('is_active', 1)
            ->get(['id', 'prodi_id', 'kode_mk', 'nama_mk', 'sks', 'semester']);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }
}
