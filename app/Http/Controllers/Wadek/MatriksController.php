<?php

namespace App\Http\Controllers\Wadek;

use App\Http\Controllers\Controller;
use App\Models\MatriksKonversi;
use App\Http\Requests\Wadek\UpdateMatriksRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatriksController extends Controller
{
    /**
     * GET /api/wadek/matriks
     * List semua 24 baris matriks konversi beserta relasi tingkatan + tahapan.
     */
    public function index(Request $request): JsonResponse
    {
        $matriks = MatriksKonversi::with([
            'tingkatan:id,nama,urutan',
            'tahapan:id,kode,nama,urutan',
            'updatedBy:id,nama',
        ])
            ->orderBy('tingkatan_id')
            ->orderBy('tahapan_id')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => $matriks,
        ]);
    }

    /**
     * PUT /api/wadek/matriks/{id}
     * Update 1 baris matriks: min_sks, max_sks, huruf_nilai.
     * Set updated_by = Wadek login.
     */
    public function update(UpdateMatriksRequest $request, int $id): JsonResponse
    {
        $matriks = MatriksKonversi::findOrFail($id);

        $matriks->update(array_merge(
            $request->validated(),
            ['updated_by' => $request->user()->id]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Matriks konversi berhasil diperbarui.',
            'data'    => $matriks->load(['tingkatan', 'tahapan', 'updatedBy:id,nama']),
        ]);
    }
}
