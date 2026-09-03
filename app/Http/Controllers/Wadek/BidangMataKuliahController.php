<?php

namespace App\Http\Controllers\Wadek;

use App\Http\Controllers\Controller;
use App\Models\BidangMataKuliah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BidangMataKuliahController extends Controller
{
    /**
     * GET /api/wadek/bidang-mata-kuliah
     * List mapping bidang → MK. Bisa filter by bidang_id atau prodi_id.
     */
    public function index(Request $request): JsonResponse
    {
        $query = BidangMataKuliah::with([
            'bidang:id,nama',
            'mataKuliah:id,kode_mk,nama_mk,sks,prodi_id',
            'mataKuliah.prodi:id,kode,singkatan,nama',
        ]);

        if ($request->filled('bidang_id')) {
            $query->where('bidang_id', (int) $request->input('bidang_id'));
        }

        if ($request->filled('prodi_id')) {
            $query->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', (int) $request->input('prodi_id')));
        }

        $data = $query->orderBy('bidang_id')->orderBy('mata_kuliah_id')->get();

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }

    /**
     * POST /api/wadek/bidang-mata-kuliah
     * Tambah mapping baru bidang → MK.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bidang_id'      => ['required', 'integer', 'exists:bidang_lomba,id'],
            'mata_kuliah_id' => ['required', 'integer', 'exists:mata_kuliah,id'],
        ], [
            'bidang_id.required'      => 'bidang_id wajib diisi.',
            'bidang_id.exists'        => 'Bidang lomba dengan ID tersebut tidak ditemukan.',
            'mata_kuliah_id.required' => 'mata_kuliah_id wajib diisi.',
            'mata_kuliah_id.exists'   => 'Mata kuliah dengan ID tersebut tidak ditemukan.',
        ]);

        // Cek duplikasi
        $exists = BidangMataKuliah::where('bidang_id', $validated['bidang_id'])
            ->where('mata_kuliah_id', $validated['mata_kuliah_id'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'mata_kuliah_id' => ['Mapping bidang → mata kuliah ini sudah ada.'],
            ]);
        }

        $mapping = BidangMataKuliah::create([
            'bidang_id'      => $validated['bidang_id'],
            'mata_kuliah_id' => $validated['mata_kuliah_id'],
            'is_active'      => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mapping bidang → mata kuliah berhasil ditambahkan.',
            'data'    => $mapping->load(['bidang:id,nama', 'mataKuliah:id,kode_mk,nama_mk,sks']),
        ], 201);
    }

    /**
     * DELETE /api/wadek/bidang-mata-kuliah/{id}
     * Hapus mapping bidang → MK (hard delete).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $mapping = BidangMataKuliah::findOrFail($id);
        $mapping->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mapping bidang → mata kuliah berhasil dihapus.',
            'data'    => null,
        ]);
    }
}
