<?php

namespace Tests\Feature;

use App\Models\BidangLomba;
use App\Models\MataKuliah;
use App\Models\Pengajuan;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MahasiswaModuleTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected User $mhsSI;
    protected User $mhsTI;
    protected User $verifSI;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mhsSI = User::where('email', 'mhs.si@test.com')->first();
        $this->mhsTI = User::where('email', 'mhs.ti@test.com')->first();
        $this->verifSI = User::where('email', 'verif.si@test.com')->first();
    }

    public function test_ref_endpoints(): void
    {
        Sanctum::actingAs($this->mhsSI);

        // 1. Ref Prodi
        $res = $this->getJson('/api/ref/prodi');
        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');

        // 2. Ref Tingkatan
        $res = $this->getJson('/api/ref/tingkatan');
        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(6, 'data');

        // 3. Ref Tahapan
        $res = $this->getJson('/api/ref/tahapan');
        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(4, 'data');

        // 4. Ref Bidang
        $res = $this->getJson('/api/ref/bidang');
        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(18, 'data');

        // 5. Ref Matriks
        $res = $this->getJson('/api/ref/matriks?tingkatan_id=1&tahapan_id=2');
        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.min_sks', 2)
            ->assertJsonPath('data.max_sks', 3)
            ->assertJsonPath('data.huruf_nilai', 'AB');

        // 6. Ref Mata Kuliah (Programming untuk SI)
        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $prodiSI = Prodi::where('singkatan', 'SI')->first();
        $res = $this->getJson("/api/ref/mata-kuliah?bidang_id={$bidang->id}&prodi_id={$prodiSI->id}");
        $res->assertStatus(200)
            ->assertJsonPath('success', true);
        $this->assertNotEmpty($res->json('data'));
    }

    public function test_submit_pengajuan_success(): void
    {
        Sanctum::actingAs($this->mhsSI);

        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mk = \App\Models\BidangMataKuliah::where('bidang_id', $bidang->id)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $this->mhsSI->prodi_id))
            ->with('mataKuliah')->first()->mataKuliah;

        $payload = [
            'nama_tim'                     => 'Tim Gemastik SI',
            'no_whatsapp'                  => '081234567890',
            'semester'                     => 6,
            'nama_lomba'                   => 'Gemastik XVII 2026',
            'bidang_id'                    => $bidang->id,
            'tingkatan_id'                 => 1, // Internasional
            'tahapan_id'                   => 2, // Lolos tahap awal (2-3 SKS, AB)
            'detail_juara'                 => 'Juara 3',
            'mata_kuliah_ids'              => [$mk->id],
            'link_sertifikat'              => 'https://drive.google.com/file/sertifikat-gemastik.pdf',
            'status_surat_tugas_mahasiswa' => 1,
            'link_surat_tugas_mahasiswa'   => 'https://drive.google.com/file/st-mhs.pdf',
            'status_surat_tugas_dosen'     => 1,
            'link_surat_tugas_dosen'       => 'https://drive.google.com/file/st-dosen.pdf',
            'link_poster'                  => 'https://drive.google.com/file/poster.jpg',
            'link_sosmed'                  => 'https://instagram.com/p/gemastik',
            'keterangan'                   => 'Submit lomba gemastik',
        ];

        $res = $this->postJson('/api/mahasiswa/pengajuan', $payload);
        $res->assertStatus(201)
            ->assertJsonPath('data.nama_lomba', 'Gemastik XVII 2026')
            ->assertJsonPath('data.snapshot_min_sks', 2)
            ->assertJsonPath('data.snapshot_max_sks', 3)
            ->assertJsonPath('data.snapshot_huruf_nilai', 'AB')
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('pengajuan', [
            'nama_lomba'                   => 'Gemastik XVII 2026',
            'user_id'                      => $this->mhsSI->id,
            'prodi_id'                     => $this->mhsSI->prodi_id,
            'link_sertifikat'              => 'https://drive.google.com/file/sertifikat-gemastik.pdf',
            'status_surat_tugas_mahasiswa' => 1,
        ]);
    }

    public function test_submit_validations(): void
    {
        Sanctum::actingAs($this->mhsSI);

        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mk = \App\Models\BidangMataKuliah::where('bidang_id', $bidang->id)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $this->mhsSI->prodi_id))
            ->with('mataKuliah')->first()->mataKuliah;

        $validPayload = [
            'no_whatsapp'                  => '081234567890',
            'semester'                     => 6,
            'nama_lomba'                   => 'Validasi Test Lomba',
            'bidang_id'                    => $bidang->id,
            'tingkatan_id'                 => 1,
            'tahapan_id'                   => 2,
            'mata_kuliah_ids'              => [$mk->id],
            'link_sertifikat'              => 'https://example.com/sertifikat.pdf',
            'link_surat_tugas_mahasiswa'   => 'https://example.com/st-mhs.pdf',
            'link_surat_tugas_dosen'       => 'https://example.com/st-dsn.pdf',
            'link_poster'                  => 'https://example.com/poster.jpg',
            'link_sosmed'                  => 'https://example.com/sosmed',
            'status_surat_tugas_mahasiswa' => 1,
            'status_surat_tugas_dosen'     => 1,
        ];

        // 1. Tanpa link_sertifikat -> 422
        $p1 = $validPayload;
        unset($p1['link_sertifikat']);
        $this->postJson('/api/mahasiswa/pengajuan', $p1)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['link_sertifikat']);

        // 2. Link_sertifikat bukan URL valid -> 422
        $p2 = $validPayload;
        $p2['link_sertifikat'] = 'bukan-url-valid';
        $this->postJson('/api/mahasiswa/pengajuan', $p2)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['link_sertifikat']);

        // 3. status_surat_tugas_mahasiswa = 1 tapi link_surat_tugas_mahasiswa null -> 422
        $p3 = $validPayload;
        $p3['status_surat_tugas_mahasiswa'] = 1;
        $p3['link_surat_tugas_mahasiswa'] = null;
        $this->postJson('/api/mahasiswa/pengajuan', $p3)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['link_surat_tugas_mahasiswa']);

        // 4. Kombinasi matriks tidak valid (Tahapan 999 tidak ada) -> 422
        $p4 = $validPayload;
        $p4['tahapan_id'] = 999;
        $this->postJson('/api/mahasiswa/pengajuan', $p4)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tahapan_id']);

        // 5. Total SKS melebihi batas maksimal matriks -> 422
        $p5 = $validPayload;
        $p5['tingkatan_id'] = 4; // Lokal / Wilayah
        $p5['tahapan_id'] = 1;   // Mendaftar (max 0 SKS), tapi pilih 1 MK 3 SKS
        $this->postJson('/api/mahasiswa/pengajuan', $p5)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['mata_kuliah_ids']);

        // 6. Duplikasi pengajuan aktif -> 422
        // Submit valid pertama kali
        $this->postJson('/api/mahasiswa/pengajuan', $validPayload)->assertStatus(201);
        // Submit ulang dengan nama lomba yang sama
        $this->postJson('/api/mahasiswa/pengajuan', $validPayload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nama_lomba']);
    }

    public function test_get_pengajuan_list_and_detail_scoping(): void
    {
        Sanctum::actingAs($this->mhsSI);

        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mkSI = \App\Models\BidangMataKuliah::where('bidang_id', $bidang->id)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $this->mhsSI->prodi_id))
            ->with('mataKuliah')->first()->mataKuliah;

        // Buat 1 pengajuan untuk mhsSI
        $payloadSI = [
            'no_whatsapp'                  => '081234567890',
            'semester'                     => 6,
            'nama_lomba'                   => 'Lomba SI 1',
            'bidang_id'                    => $bidang->id,
            'tingkatan_id'                 => 1,
            'tahapan_id'                   => 2,
            'mata_kuliah_ids'              => [$mkSI->id],
            'link_sertifikat'              => 'https://example.com/sertifikat.pdf',
            'link_surat_tugas_mahasiswa'   => 'https://example.com/st-mhs.pdf',
            'link_surat_tugas_dosen'       => 'https://example.com/st-dsn.pdf',
            'link_poster'                  => 'https://example.com/poster.jpg',
            'link_sosmed'                  => 'https://example.com/sosmed',
            'status_surat_tugas_mahasiswa' => 1,
            'status_surat_tugas_dosen'     => 1,
        ];
        $res = $this->postJson('/api/mahasiswa/pengajuan', $payloadSI);
        $pengajuanSIId = $res->json('data.id');

        // Test GET index mahasiswa SI
        $resList = $this->getJson('/api/mahasiswa/pengajuan');
        $resList->assertStatus(200)
            ->assertJsonCount(1, 'data.items');

        // Test GET show milik sendiri
        $resShow = $this->getJson("/api/mahasiswa/pengajuan/{$pengajuanSIId}");
        $resShow->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $pengajuanSIId);

        // Test GET show oleh mahasiswa TI (milik mahasiswa lain) -> 403
        Sanctum::actingAs($this->mhsTI);
        $resForbidden = $this->getJson("/api/mahasiswa/pengajuan/{$pengajuanSIId}");
        $resForbidden->assertStatus(403);
    }

    public function test_non_mahasiswa_cannot_access_mahasiswa_endpoints(): void
    {
        Sanctum::actingAs($this->verifSI);

        $this->getJson('/api/mahasiswa/pengajuan')
            ->assertStatus(403);
    }
}
