<?php

namespace Tests\Unit;

use App\Models\BidangLomba;
use App\Models\MataKuliah;
use App\Models\Pengajuan;
use App\Models\User;
use App\Services\MatriksService;
use App\Services\PengajuanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ServiceClassesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected MatriksService $matriksService;
    protected PengajuanService $pengajuanService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matriksService = app(MatriksService::class);
        $this->pengajuanService = app(PengajuanService::class);
    }

    public function test_matriks_service_snapshot_returns_correct_data(): void
    {
        // Valid (Internasional - Lolos Tahap Awal: 2-3 SKS, AB)
        $m1 = $this->matriksService->snapshot(1, 2);
        $this->assertNotNull($m1);
        $this->assertEquals(2, $m1->min_sks);
        $this->assertEquals(3, $m1->max_sks);
        $this->assertEquals('AB', $m1->huruf_nilai);

        // Valid (Nasional PKM - Pemenang: 8-12 SKS, A)
        $m2 = $this->matriksService->snapshot(3, 4);
        $this->assertNotNull($m2);
        $this->assertEquals(8, $m2->min_sks);
        $this->assertEquals(12, $m2->max_sks);
        $this->assertEquals('A', $m2->huruf_nilai);

        // Non-existent combination
        $m4 = $this->matriksService->snapshot(99, 99);
        $this->assertNull($m4);
    }

    public function test_pengajuan_service_submit_success(): void
    {
        $mhs = User::where('email', 'mhs.si@test.com')->first();
        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mk = \App\Models\BidangMataKuliah::where('bidang_id', $bidang->id)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $mhs->prodi_id))
            ->with('mataKuliah')->first()->mataKuliah;

        $data = [
            'nama_tim'                     => 'Tim Robotik SI',
            'no_whatsapp'                  => '08123456789',
            'nama_lomba'                   => 'Lomba Coding Nasional 2026',
            'bidang_id'                    => $bidang->id,
            'tingkatan_id'                 => 1, // Internasional
            'tahapan_id'                   => 2, // Lolos tahap awal (2-3 SKS, AB)
            'detail_juara'                 => 'Top 10',
            'mata_kuliah_ids'              => [$mk->id],
            'link_sertifikat'              => 'https://drive.google.com/file/sertifikat.pdf',
            'status_surat_tugas_mahasiswa' => 1,
            'link_surat_tugas_mahasiswa'   => 'https://drive.google.com/file/st-mhs.pdf',
            'status_surat_tugas_dosen'     => 0,
            'link_surat_tugas_dosen'       => null,
            'link_poster'                  => 'https://drive.google.com/file/poster.png',
            'link_sosmed'                  => 'https://instagram.com/p/example',
            'keterangan'                   => 'Keterangan pengajuan',
        ];

        $pengajuan = $this->pengajuanService->submit($data, $mhs);

        $this->assertDatabaseHas('pengajuan', [
            'id'                   => $pengajuan->id,
            'user_id'              => $mhs->id,
            'prodi_id'             => $mhs->prodi_id,
            'status'               => 'pending',
            'snapshot_min_sks'     => 2,
            'snapshot_max_sks'     => 3,
            'snapshot_huruf_nilai' => 'AB',
        ]);

        $this->assertDatabaseHas('pengajuan_mata_kuliah', [
            'pengajuan_id'   => $pengajuan->id,
            'mata_kuliah_id' => $mk->id,
            'sks_snapshot'   => $mk->sks,
            'huruf_nilai'    => null,
        ]);
    }

    public function test_pengajuan_service_rejects_duplicate_active_submission(): void
    {
        $mhs = User::where('email', 'mhs.si@test.com')->first();
        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mk = \App\Models\BidangMataKuliah::where('bidang_id', $bidang->id)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $mhs->prodi_id))
            ->with('mataKuliah')->first()->mataKuliah;

        $data = [
            'no_whatsapp'     => '08123456789',
            'nama_lomba'      => 'Gemastik 2026',
            'bidang_id'       => $bidang->id,
            'tingkatan_id'    => 1,
            'tahapan_id'      => 2,
            'mata_kuliah_ids' => [$mk->id],
            'link_sertifikat' => 'https://example.com/sertifikat.pdf',
        ];

        // First submit -> Success
        $this->pengajuanService->submit($data, $mhs);

        // Second submit with same nama_lomba -> Expected ValidationException
        $this->expectException(ValidationException::class);
        $this->pengajuanService->submit($data, $mhs);
    }

    public function test_pengajuan_service_rejects_invalid_matriks_combination(): void
    {
        $mhs = User::where('email', 'mhs.si@test.com')->first();
        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mk = \App\Models\BidangMataKuliah::where('bidang_id', $bidang->id)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $mhs->prodi_id))
            ->with('mataKuliah')->first()->mataKuliah;

        $data = [
            'no_whatsapp'     => '08123456789',
            'nama_lomba'      => 'Lomba Invalid Matriks',
            'bidang_id'       => $bidang->id,
            'tingkatan_id'    => 1, // Internasional
            'tahapan_id'      => 999, // Non-existent combination
            'mata_kuliah_ids' => [$mk->id],
            'link_sertifikat' => 'https://example.com/sertifikat.pdf',
        ];

        $this->expectException(ValidationException::class);
        $this->pengajuanService->submit($data, $mhs);
    }

    public function test_pengajuan_service_rejects_out_of_range_sks(): void
    {
        $mhs = User::where('email', 'mhs.si@test.com')->first();
        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mk = \App\Models\BidangMataKuliah::where('bidang_id', $bidang->id)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $mhs->prodi_id))
            ->with('mataKuliah')->first()->mataKuliah;

        // Tahapan dengan max_sks = 0 (hanya portofolio/SKPI), tetapi memilih MK
        $data = [
            'no_whatsapp'     => '08123456789',
            'nama_lomba'      => 'Lomba SKS Melebihi',
            'bidang_id'       => $bidang->id,
            'tingkatan_id'    => 4, // Lokal
            'tahapan_id'      => 1, // Mendaftar (max 0 SKS)
            'mata_kuliah_ids' => [$mk->id],
            'link_sertifikat' => 'https://example.com/sertifikat.pdf',
        ];

        $this->expectException(ValidationException::class);
        $this->pengajuanService->submit($data, $mhs);
    }

    public function test_pengajuan_service_rejects_wrong_prodi_or_bidang_course(): void
    {
        $mhs = User::where('email', 'mhs.si@test.com')->first();
        $bidang = BidangLomba::where('nama', 'Programming')->first();

        // MK TI chosen by SI student -> Expected ValidationException
        $mkTI = \App\Models\BidangMataKuliah::where('bidang_id', $bidang->id)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', 2)) // TI
            ->with('mataKuliah')->first()->mataKuliah;

        $data = [
            'no_whatsapp'     => '08123456789',
            'nama_lomba'      => 'Lomba Wrong Prodi MK',
            'bidang_id'       => $bidang->id,
            'tingkatan_id'    => 1,
            'tahapan_id'      => 2,
            'mata_kuliah_ids' => [$mkTI->id],
            'link_sertifikat' => 'https://example.com/sertifikat.pdf',
        ];

        $this->expectException(ValidationException::class);
        $this->pengajuanService->submit($data, $mhs);
    }

    public function test_full_workflow_verifikasi_and_finalisasi(): void
    {
        $mhs = User::where('email', 'mhs.si@test.com')->first();
        $verifSI = User::where('email', 'verif.si@test.com')->first();
        $verifTI = User::where('email', 'verif.ti@test.com')->first();
        $tendik = User::where('email', 'tendik@test.com')->first();

        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mk = \App\Models\BidangMataKuliah::where('bidang_id', $bidang->id)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $mhs->prodi_id))
            ->with('mataKuliah')->first()->mataKuliah;

        $data = [
            'no_whatsapp'     => '08123456789',
            'nama_lomba'      => 'Workflow Test Lomba',
            'bidang_id'       => $bidang->id,
            'tingkatan_id'    => 1,
            'tahapan_id'      => 2, // 2-3 SKS, snapshot_huruf_nilai: AB
            'mata_kuliah_ids' => [$mk->id],
            'link_sertifikat' => 'https://example.com/sertifikat.pdf',
        ];

        // 1. Submit pengajuan
        $pengajuan = $this->pengajuanService->submit($data, $mhs);
        $this->assertEquals('pending', $pengajuan->status);

        // 2. Verifikator TI (wrong prodi) coba proses -> 403
        try {
            $this->pengajuanService->terima($pengajuan, $verifTI);
            $this->fail('Harusnya gagal 403 karena verifikator TI di luar scope prodi SI.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }

        // 3. Verifikator SI terima
        $pengajuanDiterima = $this->pengajuanService->terima($pengajuan, $verifSI);
        $this->assertEquals('diterima', $pengajuanDiterima->status);
        $this->assertEquals($verifSI->id, $pengajuanDiterima->verifikator_id);
        $this->assertNotNull($pengajuanDiterima->verified_at);

        // 4. Tendik finalisasi dengan nilai salah (misal A bukan AB) -> ValidationException
        try {
            $this->pengajuanService->finalisasi($pengajuanDiterima, $tendik, [
                'nilai_per_mk' => [
                    ['mk_id' => $mk->id, 'huruf_nilai' => 'A'], // Harus AB
                ],
            ]);
            $this->fail('Harusnya gagal karena huruf nilai A tidak sama dengan snapshot AB.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('nilai_per_mk', $e->errors());
        }

        // 5. Tendik finalisasi dengan nilai persis sesuai snapshot (AB) -> Sukses selesai
        $pengajuanSelesai = $this->pengajuanService->finalisasi($pengajuanDiterima, $tendik, [
            'link_sk_konversi' => 'https://drive.google.com/file/sk-konversi.pdf',
            'nilai_per_mk'     => [
                ['mk_id' => $mk->id, 'huruf_nilai' => 'AB'],
            ],
        ]);

        $this->assertEquals('selesai', $pengajuanSelesai->status);
        $this->assertEquals($tendik->id, $pengajuanSelesai->tendik_id);
        $this->assertNotNull($pengajuanSelesai->processed_at);
        $this->assertEquals('https://drive.google.com/file/sk-konversi.pdf', $pengajuanSelesai->link_sk_konversi);

        $this->assertDatabaseHas('pengajuan_mata_kuliah', [
            'pengajuan_id'   => $pengajuan->id,
            'mata_kuliah_id' => $mk->id,
            'huruf_nilai'    => 'AB',
        ]);
    }
}
