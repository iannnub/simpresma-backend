<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\Prodi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard/statistik
     * Statistik pengajuan per prodi: total + persentase dari grand total.
     * Accessible oleh semua role yang terautentikasi.
     */
    public function statistik(Request $request): JsonResponse
    {
        // Ambil semua prodi sebagai base (termasuk prodi dengan 0 pengajuan)
        $prodis = Prodi::orderBy('id')->get();

        // Hitung total per prodi (semua status)
        $grandTotal = Pengajuan::count();

        // Query group by prodi_id
        $countPerProdi = Pengajuan::selectRaw('prodi_id, COUNT(*) as total')
            ->groupBy('prodi_id')
            ->pluck('total', 'prodi_id');

        // Hitung juga breakdown per status (opsional tapi informatif)
        $statusPerProdi = Pengajuan::selectRaw('prodi_id, status, COUNT(*) as total')
            ->groupBy('prodi_id', 'status')
            ->get()
            ->groupBy('prodi_id');

        $data = $prodis->map(function (Prodi $prodi) use ($countPerProdi, $statusPerProdi, $grandTotal) {
            $total      = (int) ($countPerProdi[$prodi->id] ?? 0);
            $persentase = $grandTotal > 0
                ? round(($total / $grandTotal) * 100, 2)
                : 0.0;

            // Breakdown per status untuk prodi ini
            $statusRows   = $statusPerProdi->get($prodi->id, collect());
            $byStatus     = $statusRows->pluck('total', 'status')->toArray();

            return [
                'prodi_id'   => $prodi->id,
                'prodi'      => $prodi->singkatan,
                'nama_prodi' => $prodi->nama,
                'total'      => $total,
                'persentase' => $persentase,
                'by_status'  => [
                    'pending'  => (int) ($byStatus['pending']  ?? 0),
                    'diterima' => (int) ($byStatus['diterima'] ?? 0),
                    'ditolak'  => (int) ($byStatus['ditolak']  ?? 0),
                    'selesai'  => (int) ($byStatus['selesai']  ?? 0),
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => [
                'grand_total' => $grandTotal,
                'per_prodi'   => $data,
            ],
        ]);
    }
}
