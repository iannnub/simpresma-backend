<?php

namespace App\Http\Controllers\Tendik;

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
     * GET /api/tendik/export
     * Export pengajuan berstatus "diterima" dan "selesai" (Tendik tidak bisa ekspor semua).
     *
     * Query params:
     *   - format : xlsx|csv  (default: xlsx)
     *   - status : diterima|selesai  (default: keduanya)
     *   - start  : Y-m-d  (opsional)
     *   - end    : Y-m-d  (opsional)
     */
    public function export(Request $request): BinaryFileResponse|StreamedResponse|Response
    {
        $request->validate([
            'format' => ['sometimes', 'string', 'in:xlsx,csv'],
            'status' => ['sometimes', 'string', 'in:diterima,selesai'],
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

        // Tendik: hanya bisa lihat data yang sudah di-approve/selesai
        if ($request->filled('status') && in_array($request->input('status'), ['diterima', 'selesai'])) {
            $query->where('status', $request->input('status'));
        } else {
            $query->whereIn('status', ['diterima', 'selesai']);
        }

        if ($request->filled('start')) {
            $query->whereDate('created_at', '>=', $request->input('start'));
        }
        if ($request->filled('end')) {
            $query->whereDate('created_at', '<=', $request->input('end'));
        }

        $data = $query->orderBy('verified_at', 'desc')->get();

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
