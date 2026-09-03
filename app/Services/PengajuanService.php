<?php

namespace App\Services;

use App\Events\PengajuanStatusChanged;
use App\Models\BidangMataKuliah;
use App\Models\MataKuliah;
use App\Models\Pengajuan;
use App\Models\PengajuanMataKuliah;
use App\Models\User;
use App\Models\VerifikatorProdi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PengajuanService
{
    public function __construct(
        protected MatriksService $matriksService
    ) {}

    /**
     * Submit pengajuan prestasi mahasiswa.
     *
     * @throws ValidationException
     */
    public function submit(array $data, User $mahasiswa): Pengajuan
    {
        if (!$mahasiswa->prodi_id) {
            throw ValidationException::withMessages([
                'prodi_id' => ['Akun mahasiswa belum memiliki program studi yang terdaftar.'],
            ]);
        }

        // 1. Validasi duplikasi pengajuan aktif
        $isDuplicate = Pengajuan::where('user_id', $mahasiswa->id)
            ->where('nama_lomba', $data['nama_lomba'])
            ->whereIn('status', ['pending', 'diterima'])
            ->exists();

        if ($isDuplicate) {
            throw ValidationException::withMessages([
                'nama_lomba' => ['Pengajuan untuk nama lomba ini masih dalam proses aktif (status pending atau diterima).'],
            ]);
        }

        // 2. Lookup & Snapshot Matriks
        $matriks = $this->matriksService->snapshot(
            (int) $data['tingkatan_id'],
            (int) $data['tahapan_id']
        );

        $hasConversion = $matriks && $matriks->min_sks !== null && $matriks->max_sks !== null;

        // 3. Validasi Mata Kuliah jika ada yang dipilih oleh mahasiswa
        if (!empty($data['mata_kuliah_ids']) && is_array($data['mata_kuliah_ids'])) {
            $maxAllowedSks = $hasConversion ? (int) $matriks->max_sks : 0;
            $mataKuliahs = $this->validateMataKuliah(
                $data['mata_kuliah_ids'],
                (int) $data['bidang_id'],
                (int) $mahasiswa->prodi_id,
                $maxAllowedSks
            );
        } else {
            $mataKuliahs = collect();
        }

        // 4. Atomic Transaction: Simpan Pengajuan & Pivot Mata Kuliah
        return DB::transaction(function () use ($data, $mahasiswa, $matriks, $hasConversion, $mataKuliahs) {
            $pengajuan = Pengajuan::create([
                'user_id'                      => $mahasiswa->id,
                'prodi_id'                     => $mahasiswa->prodi_id,
                'nama_tim'                     => $data['nama_tim'] ?? null,
                'no_whatsapp'                  => $data['no_whatsapp'],
                'nama_lomba'                   => $data['nama_lomba'],
                'bidang_id'                    => $data['bidang_id'],
                'tingkatan_id'                 => $data['tingkatan_id'],
                'tahapan_id'                   => $data['tahapan_id'],
                'semester'                     => $data['semester'] ?? null,
                'detail_juara'                 => $data['detail_juara'] ?? null,
                'snapshot_min_sks'             => $hasConversion ? $matriks->min_sks : null,
                'snapshot_max_sks'             => $hasConversion ? $matriks->max_sks : null,
                'snapshot_huruf_nilai'         => $hasConversion ? $matriks->huruf_nilai : null,
                'link_sertifikat'              => $data['link_sertifikat'],
                'status_surat_tugas_mahasiswa' => !empty($data['link_surat_tugas_mahasiswa']) ? 1 : (int) ($data['status_surat_tugas_mahasiswa'] ?? 0),
                'link_surat_tugas_mahasiswa'   => $data['link_surat_tugas_mahasiswa'] ?? null,
                'status_surat_tugas_dosen'     => !empty($data['link_surat_tugas_dosen']) ? 1 : (int) ($data['status_surat_tugas_dosen'] ?? 0),
                'link_surat_tugas_dosen'       => $data['link_surat_tugas_dosen'] ?? null,
                'link_poster'                  => $data['link_poster'] ?? null,
                'link_sosmed'                  => $data['link_sosmed'] ?? null,
                'keterangan'                   => $data['keterangan'] ?? null,
                'status'                       => 'pending',
            ]);

            foreach ($mataKuliahs as $mk) {
                PengajuanMataKuliah::create([
                    'pengajuan_id'   => $pengajuan->id,
                    'mata_kuliah_id' => $mk->id,
                    'sks_snapshot'   => $mk->sks,
                    'huruf_nilai'    => null,
                ]);
            }

            return $pengajuan->load(['mataKuliahs', 'bidang', 'tingkatan', 'tahapan', 'prodi', 'mahasiswa']);
        });

        // Kirim notifikasi Telegram (ke Mahasiswa & Verifikator) saat pengajuan berhasil disubmit
        event(new \App\Events\PengajuanStatusChanged($result, 'pending', $mahasiswa));

        return $result;
    }

    /**
     * Validasi mata kuliah yang dipilih mahasiswa:
     * - Tidak duplikat dalam list
     * - Terdaftar aktif pada mapping bidang dan prodi mahasiswa
     * - Total SKS berada di dalam rentang [min_sks, max_sks]
     *
     * @throws ValidationException
     */
    public function validateMataKuliah(
        array $mkIds,
        int $bidangId,
        int $prodiId,
        int $maxSks = 0
    ): Collection {
        if (empty($mkIds)) {
            return new Collection();
        }

        $uniqueMkIds = array_unique($mkIds);
        if (count($uniqueMkIds) !== count($mkIds)) {
            throw ValidationException::withMessages([
                'mata_kuliah_ids' => ['Terdapat mata kuliah yang duplikat dalam pilihan.'],
            ]);
        }

        // Scope validation: MK harus aktif, prodi sesuai, dan terhubung ke bidang_lomba
        $validMkIds = BidangMataKuliah::where('bidang_id', $bidangId)
            ->where('is_active', 1)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $prodiId)->where('is_active', 1))
            ->pluck('mata_kuliah_id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        foreach ($mkIds as $mkId) {
            if (!in_array((int) $mkId, $validMkIds, true)) {
                throw ValidationException::withMessages([
                    'mata_kuliah_ids' => ['Satu atau lebih mata kuliah yang dipilih tidak sesuai dengan bidang lomba dan program studi mahasiswa.'],
                ]);
            }
        }

        $mataKuliahs = MataKuliah::whereIn('id', $mkIds)->get();
        $totalSks = $mataKuliahs->sum('sks');

        // Validasi batas maksimum: mahasiswa diperbolehkan mengambil di bawah min_sks jika sisa MK kurikulumnya sedikit
        if ($maxSks === 0 && $totalSks > 0) {
            throw ValidationException::withMessages([
                'mata_kuliah_ids' => ["Tahapan lomba ini tidak menyediakan konversi SKS (hanya pencatatan portofolio/SKPI). Tidak dapat memilih mata kuliah."],
            ]);
        }

        if ($maxSks > 0 && $totalSks > $maxSks) {
            throw ValidationException::withMessages([
                'mata_kuliah_ids' => ["Total SKS mata kuliah yang dipilih ({$totalSks} SKS) melebihi batas maksimum konversi ({$maxSks} SKS)."],
            ]);
        }

        return $mataKuliahs;
    }

    /**
     * Verifikator menerima pengajuan.
     *
     * @throws ValidationException
     */
    public function terima(Pengajuan $pengajuan, User $verifikator, ?string $feedback = null): Pengajuan
    {
        $this->validateVerifikatorScope($verifikator, $pengajuan->prodi_id);

        if ($pengajuan->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Hanya pengajuan dengan status pending yang dapat diverifikasi.'],
            ]);
        }

        $pengajuan->update([
            'status'               => 'diterima',
            'feedback_verifikator' => $feedback ?: 'Pengajuan telah diperiksa dan disetujui oleh Dosen Verifikator.',
            'verifikator_id'       => $verifikator->id,
            'verified_at'          => now(),
        ]);

        $fresh = $pengajuan->fresh(['mataKuliahs', 'bidang', 'tingkatan', 'tahapan', 'prodi', 'verifikator', 'mahasiswa']);

        // Kirim notifikasi Telegram (async via queue)
        event(new PengajuanStatusChanged($fresh, 'diterima', $verifikator));

        return $fresh;
    }

    /**
     * Verifikator menolak pengajuan.
     *
     * @throws ValidationException
     */
    public function tolak(Pengajuan $pengajuan, User $verifikator, string $feedback): Pengajuan
    {
        $this->validateVerifikatorScope($verifikator, $pengajuan->prodi_id);

        if ($pengajuan->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Hanya pengajuan dengan status pending yang dapat diverifikasi.'],
            ]);
        }

        if (trim($feedback) === '') {
            throw ValidationException::withMessages([
                'feedback_verifikator' => ['Feedback atau alasan penolakan wajib diisi jika menolak pengajuan.'],
            ]);
        }

        $pengajuan->update([
            'status'               => 'ditolak',
            'feedback_verifikator' => $feedback,
            'verifikator_id'       => $verifikator->id,
            'verified_at'          => now(),
        ]);

        $fresh = $pengajuan->fresh(['mataKuliahs', 'bidang', 'tingkatan', 'tahapan', 'prodi', 'verifikator', 'mahasiswa']);

        // Kirim notifikasi Telegram (async via queue)
        event(new PengajuanStatusChanged($fresh, 'ditolak', $verifikator));

        return $fresh;
    }

    /**
     * Tendik finalisasi konversi nilai (strict = snapshot_huruf_nilai).
     *
     * @throws ValidationException
     */
    public function finalisasi(Pengajuan $pengajuan, User $tendik, array $data): Pengajuan
    {
        if ($pengajuan->status !== 'diterima') {
            throw ValidationException::withMessages([
                'status' => ['Hanya pengajuan berstatus diterima yang dapat difinalisasi oleh Tendik.'],
            ]);
        }

        $nilaiList = $data['nilai_per_mk'] ?? [];
        $pengajuanMks = $pengajuan->pengajuanMataKuliahs()->get();

        $inputMap = collect($nilaiList)->keyBy('mk_id');

        // Jika ada mata kuliah yang dikonversi, validasi huruf nilainya sesuai snapshot
        if ($pengajuanMks->isNotEmpty()) {
            foreach ($pengajuanMks as $pmk) {
                if (!$inputMap->has($pmk->mata_kuliah_id)) {
                    throw ValidationException::withMessages([
                        'nilai_per_mk' => ["Nilai untuk seluruh mata kuliah yang dipilih wajib diisi."],
                    ]);
                }

                $inputNilai = trim($inputMap->get($pmk->mata_kuliah_id)['huruf_nilai'] ?? '');
                if ($inputNilai !== $pengajuan->snapshot_huruf_nilai) {
                    throw ValidationException::withMessages([
                        'nilai_per_mk' => ["Huruf nilai yang diinput ({$inputNilai}) wajib persis sama dengan nilai matriks ({$pengajuan->snapshot_huruf_nilai})."],
                    ]);
                }
            }
        }

        $result = DB::transaction(function () use ($pengajuan, $pengajuanMks, $inputMap, $tendik, $data) {
            if ($pengajuanMks->isNotEmpty()) {
                foreach ($pengajuanMks as $pmk) {
                    $inputNilai = $inputMap->get($pmk->mata_kuliah_id)['huruf_nilai'];
                    $pmk->update(['huruf_nilai' => $inputNilai]);
                }
            }

            $pengajuan->update([
                'link_sk_konversi' => $data['link_sk_konversi'] ?? null,
                'catatan_tendik'   => $data['catatan_tendik'] ?? null,
                'tendik_id'        => $tendik->id,
                'processed_at'     => now(),
                'status'           => 'selesai',
            ]);

            return $pengajuan->fresh(['mataKuliahs', 'bidang', 'tingkatan', 'tahapan', 'prodi', 'verifikator', 'tendik', 'mahasiswa']);
        });

        // Kirim notifikasi Telegram setelah transaction commit (async via queue)
        event(new PengajuanStatusChanged($result, 'selesai', $tendik));

        return $result;
    }

    /**
     * Memastikan verifikator memiliki hak akses aktif pada prodi pengajuan.
     */
    protected function validateVerifikatorScope(User $verifikator, int $prodiId): void
    {
        $isInScope = VerifikatorProdi::where('user_id', $verifikator->id)
            ->where('prodi_id', $prodiId)
            ->where('is_active', 1)
            ->exists();

        if (!$isInScope) {
            abort(403, 'Anda tidak memiliki wewenang verifikasi untuk program studi pengajuan ini.');
        }
    }
}
