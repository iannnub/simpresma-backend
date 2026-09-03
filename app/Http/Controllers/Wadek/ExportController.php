<?php

namespace App\Http\Controllers\Wadek;

use App\Exports\PengajuanExport;
use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * GET /api/wadek/export
     * Export semua pengajuan (Wadek bisa lihat semua prodi).
     *
     * Query params:
     *   - format : xlsx|csv  (default: xlsx)
     *   - prodi  : singkatan prodi opsional, misal: SI, TI, IF
     *   - status : pending|diterima|ditolak|selesai  (opsional)
     *   - start  : Y-m-d  (opsional)
     *   - end    : Y-m-d  (opsional)
     */
    public function export(Request $request): BinaryFileResponse|StreamedResponse|Response
    {
        $request->validate([
            'format' => ['sometimes', 'string', 'in:xlsx,csv'],
            'prodi'  => ['sometimes', 'string'],
            'status' => ['sometimes', 'string', 'in:pending,diterima,ditolak,selesai'],
            'start'  => ['sometimes', 'date_format:Y-m-d'],
            'end'    => ['sometimes', 'date_format:Y-m-d'],
        ]);

        $query = Pengajuan::with([
            'mahasiswa:id,nama,nim_nip',
            'prodi:id,kode,singkatan,nama',
            'bidang:id,nama',
            'tingkatan:id,nama',
            'tahapan:id,nama',
            'verifikator:id,nama',
            'pengajuanMataKuliahs.mataKuliah:id,kode_mk,nama_mk,sks',
        ]);

        // Filter opsional
        if ($request->filled('prodi')) {
            $query->whereHas('prodi', fn ($q) => $q->where('singkatan', strtoupper($request->input('prodi'))));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('start')) {
            $query->whereDate('created_at', '>=', $request->input('start'));
        }
        if ($request->filled('end')) {
            $query->whereDate('created_at', '<=', $request->input('end'));
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        return $this->download($data, $request->input('format', 'xlsx'));
    }

    // ─── Helper download ─────────────────────────────────────────────────

    protected function download(\Illuminate\Support\Collection $data, string $format): BinaryFileResponse|StreamedResponse|Response
    {
        $timestamp = now()->format('Y-m-d-His');
        $filename  = "laporan-prestasi-{$timestamp}.{$format}";

        $writerType = match ($format) {
            'csv'  => \Maatwebsite\Excel\Excel::CSV,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        return Excel::download(new PengajuanExport($data), $filename, $writerType, [
            'Access-Control-Expose-Headers' => 'Content-Disposition',
        ]);
    }
}
