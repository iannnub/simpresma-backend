<?php

namespace App\Exports;

use App\Models\Pengajuan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PengajuanExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles,
    WithTitle
{
    protected Collection $data;

    /**
     * @param Collection $data  — koleksi Pengajuan yang sudah di-load dengan relasi lengkap
     */
    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    // ─── Data Source ──────────────────────────────────────────────────────

    public function collection(): Collection
    {
        return $this->data;
    }

    // ─── Sheet Title ──────────────────────────────────────────────────────

    public function title(): string
    {
        return 'Laporan Prestasi Mahasiswa';
    }

    // ─── 17-Column Headings ───────────────────────────────────────────────

    public function headings(): array
    {
        return [
            'No',
            'NIM',
            'Nama Mahasiswa',
            'Program Studi',
            'Nama Lomba',
            'Bidang Lomba',
            'Tingkatan',
            'Tahapan / Capaian',
            'Tanggal Pengajuan',
            'Status',
            'SKS Dikonversi',
            'Mata Kuliah',
            'Nilai Akhir',
            'Link Sertifikat',
            'Link SK',
            'Verifikator',
            'Tanggal Verifikasi',
        ];
    }

    // ─── Row Mapping ──────────────────────────────────────────────────────

    public function map($row): array
    {
        static $no = 0;
        $no++;

        /** @var Pengajuan $row */

        // Kumpulkan nama MK & nilai akhir
        $mataKuliahs = $row->pengajuanMataKuliahs ?? collect();
        $mkNames  = $mataKuliahs->map(fn($pmk) => $pmk->mataKuliah?->nama_mk ?? '')->implode(', ');
        $mkNilai  = $mataKuliahs->map(fn($pmk) => $pmk->huruf_nilai ?? '-')->implode(', ');
        $totalSks = $mataKuliahs->map(fn($pmk) => $pmk->mataKuliah?->sks ?? 0)->sum();

        // Format tanggal
        $tglPengajuan   = $row->created_at?->format('d/m/Y') ?? '-';
        $tglVerifikasi  = $row->verified_at?->format('d/m/Y H:i') ?? '-';

        // Status dengan kapital
        $statusMap = [
            'pending'  => 'Pending',
            'diterima' => 'Diterima',
            'ditolak'  => 'Ditolak',
            'selesai'  => 'Selesai',
        ];

        return [
            $no,
            $row->mahasiswa?->nim_nip ?? '-',
            $row->mahasiswa?->nama ?? '-',
            $row->prodi?->singkatan ?? '-',
            $row->nama_lomba ?? '-',
            $row->bidang?->nama ?? '-',
            $row->tingkatan?->nama ?? '-',
            $row->tahapan?->nama ?? '-',
            $tglPengajuan,
            $statusMap[$row->status] ?? $row->status,
            $totalSks,
            $mkNames ?: '-',
            $mkNilai ?: '-',
            $row->link_sertifikat ?? '-',
            $row->link_sk_konversi ?? '-',
            $row->verifikator?->nama ?? '-',
            $tglVerifikasi,
        ];
    }

    // ─── Styles ───────────────────────────────────────────────────────────

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row: bold + background warna biru gelap + teks putih
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill'      => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A5F'],
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
