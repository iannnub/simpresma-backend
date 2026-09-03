<?php

namespace Tests\Feature;

use App\Models\BidangLomba;
use App\Models\MataKuliah;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TendikModuleTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected User $mhsSI;
    protected User $mhsTI;
    protected User $verifSI;
    protected User $tendik;
    protected User $verifTI;

    protected function setUp(): void
    {
        parent::setUp();;
        $this->mhsSI   = User::where('email', 'mhs.si@test.com')->first();
        $this->mhsTI   = User::where('email', 'mhs.ti@test.com')->first();
        $this->verifSI = User::where('email', 'verif.si@test.com')->first();
        $this->verifTI = User::where('email', 'verif.ti@test.com')->first();
        $this->tendik  = User::where('email', 'tendik@test.com')->first();
    }

    // ─────────────────────────────────────────────────────────────
    // Helper: submit + terima pengajuan SI
    // Returns [$pengajuanId, $mkId, $snapshotHurufNilai]
    // ─────────────────────────────────────────────────────────────
    private function buatPengajuanDiterima(): array
    {
        // 1. Mahasiswa SI submit pengajuan (tingkatan=1 Internasional, tahapan=2 → snapshot AB)
        Sanctum::actingAs($this->mhsSI);

        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mk     = \App\Models\BidangMataKuliah::where('bidang_id', $bidang->id)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $this->mhsSI->prodi_id))
            ->with('mataKuliah')->first()->mataKuliah;

        $payload = [
            'no_whatsapp'                  => '081234567890',
            'semester'                     => 6,
            'nama_lomba'                   => 'Gemastik XVII 2026',
            'bidang_id'                    => $bidang->id,
            'tingkatan_id'                 => 1, // Internasional
            'tahapan_id'                   => 2, // Lolos tahap awal → min=2, max=3, snapshot=AB
            'mata_kuliah_ids'              => [$mk->id],
            'link_sertifikat'              => 'https://drive.google.com/sertifikat.pdf',
            'link_surat_tugas_mahasiswa'   => 'https://drive.google.com/st-mhs.pdf',
            'link_surat_tugas_dosen'       => 'https://drive.google.com/st-dsn.pdf',
            'link_poster'                  => 'https://drive.google.com/poster.jpg',
            'link_sosmed'                  => 'https://instagram.com/p/prestasi',
            'status_surat_tugas_mahasiswa' => 1,
            'status_surat_tugas_dosen'     => 1,
        ];

        $resSubmit = $this->postJson('/api/mahasiswa/pengajuan', $payload);
        $resSubmit->assertStatus(201);
        $pengajuanId      = $resSubmit->json('data.id');
        $snapshotNilai    = $resSubmit->json('data.snapshot_huruf_nilai'); // 'AB'

        // 2. Verifikator SI menerima pengajuan
        Sanctum::actingAs($this->verifSI);
        $this->postJson("/api/verifikator/pengajuan/{$pengajuanId}/terima")->assertStatus(200);

        return [$pengajuanId, $mk->id, $snapshotNilai];
    }

    // ─────────────────────────────────────────────────────────────
    // T9.2.1 — GET /api/tendik/pengajuan hanya tampil status 'diterima'
    // ─────────────────────────────────────────────────────────────
    public function test_tendik_hanya_tampil_pengajuan_diterima(): void
    {
        $this->buatPengajuanDiterima();

        Sanctum::actingAs($this->tendik);
        $res = $this->getJson('/api/tendik/pengajuan');

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.meta.total', 1);

        // Semua item harus berstatus 'diterima'
        foreach ($res->json('data.items') as $item) {
            $this->assertEquals('diterima', $item['status']);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // T9.2.2 — show: tampil list MK + snapshot_huruf_nilai sebagai referensi
    // ─────────────────────────────────────────────────────────────
    public function test_show_pengajuan_tampil_snapshot_dan_list_mk(): void
    {
        [$pengajuanId, $mkId, $snapshotNilai] = $this->buatPengajuanDiterima();

        Sanctum::actingAs($this->tendik);
        $res = $this->getJson("/api/tendik/pengajuan/{$pengajuanId}");

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $pengajuanId)
            ->assertJsonPath('data.status', 'diterima');

        // snapshot_huruf_nilai harus ada dan sesuai
        $this->assertEquals($snapshotNilai, $res->json('data.snapshot_huruf_nilai'));

        // pengajuan_mata_kuliahs (list MK) harus ada
        $this->assertNotEmpty($res->json('data.pengajuan_mata_kuliahs'));

        // Relasi verifikator harus ada (sudah diterima)
        $this->assertNotNull($res->json('data.verifikator'));
    }

    // ─────────────────────────────────────────────────────────────
    // T9.2.3 — finalisasi dengan huruf_nilai SAMA dengan snapshot → berhasil
    // ─────────────────────────────────────────────────────────────
    public function test_finalisasi_dengan_nilai_sesuai_snapshot_berhasil(): void
    {
        [$pengajuanId, $mkId, $snapshotNilai] = $this->buatPengajuanDiterima();

        Sanctum::actingAs($this->tendik);
        $res = $this->postJson("/api/tendik/pengajuan/{$pengajuanId}/finalisasi", [
            'nilai_per_mk' => [
                ['mk_id' => $mkId, 'huruf_nilai' => $snapshotNilai], // 'AB'
            ],
            'link_sk_konversi' => 'https://drive.google.com/sk-konversi.pdf',
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'selesai');

        // Pastikan DB terupdate
        $this->assertDatabaseHas('pengajuan', [
            'id'               => $pengajuanId,
            'status'           => 'selesai',
            'tendik_id'        => $this->tendik->id,
            'link_sk_konversi' => 'https://drive.google.com/sk-konversi.pdf',
        ]);

        // processed_at harus terisi
        $pengajuan = Pengajuan::find($pengajuanId);
        $this->assertNotNull($pengajuan->processed_at);

        // huruf_nilai di pengajuan_mata_kuliah harus tersimpan
        $this->assertDatabaseHas('pengajuan_mata_kuliah', [
            'pengajuan_id'   => $pengajuanId,
            'mata_kuliah_id' => $mkId,
            'huruf_nilai'    => $snapshotNilai,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // T9.2.4 — finalisasi dengan huruf_nilai BERBEDA dari snapshot → 422
    // ─────────────────────────────────────────────────────────────
    public function test_finalisasi_dengan_nilai_berbeda_dari_snapshot_return_422(): void
    {
        [$pengajuanId, $mkId, $snapshotNilai] = $this->buatPengajuanDiterima();

        // snapshot = 'AB', kita kirim 'A' (berbeda)
        $this->assertNotEquals('A', $snapshotNilai, 'Snapshot harus bukan A agar test bermakna');

        Sanctum::actingAs($this->tendik);
        $res = $this->postJson("/api/tendik/pengajuan/{$pengajuanId}/finalisasi", [
            'nilai_per_mk' => [
                ['mk_id' => $mkId, 'huruf_nilai' => 'A'], // BEDA dari snapshot 'AB'
            ],
        ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['nilai_per_mk']);

        // Status harus TIDAK berubah
        $this->assertDatabaseHas('pengajuan', [
            'id'     => $pengajuanId,
            'status' => 'diterima',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // T9.2.5 — finalisasi tanpa link_sk_konversi tetap berhasil (opsional)
    // ─────────────────────────────────────────────────────────────
    public function test_finalisasi_tanpa_link_sk_konversi_tetap_berhasil(): void
    {
        [$pengajuanId, $mkId, $snapshotNilai] = $this->buatPengajuanDiterima();

        Sanctum::actingAs($this->tendik);
        $res = $this->postJson("/api/tendik/pengajuan/{$pengajuanId}/finalisasi", [
            'nilai_per_mk' => [
                ['mk_id' => $mkId, 'huruf_nilai' => $snapshotNilai],
            ],
            // link_sk_konversi tidak dikirim
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'selesai');

        $this->assertDatabaseHas('pengajuan', [
            'id'               => $pengajuanId,
            'status'           => 'selesai',
            'link_sk_konversi' => null,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // T9.2.6 — finalisasi dengan link_sk_konversi URL valid → tersimpan
    // ─────────────────────────────────────────────────────────────
    public function test_finalisasi_dengan_link_sk_konversi_url_valid_tersimpan(): void
    {
        [$pengajuanId, $mkId, $snapshotNilai] = $this->buatPengajuanDiterima();

        Sanctum::actingAs($this->tendik);
        $res = $this->postJson("/api/tendik/pengajuan/{$pengajuanId}/finalisasi", [
            'nilai_per_mk' => [
                ['mk_id' => $mkId, 'huruf_nilai' => $snapshotNilai],
            ],
            'link_sk_konversi' => 'https://siakad.example.com/sk/12345.pdf',
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('data.link_sk_konversi', 'https://siakad.example.com/sk/12345.pdf');
    }

    // ─────────────────────────────────────────────────────────────
    // T9.2.7 — finalisasi dengan link_sk_konversi bukan URL valid → 422
    // ─────────────────────────────────────────────────────────────
    public function test_finalisasi_dengan_link_sk_konversi_bukan_url_return_422(): void
    {
        [$pengajuanId, $mkId, $snapshotNilai] = $this->buatPengajuanDiterima();

        Sanctum::actingAs($this->tendik);
        $res = $this->postJson("/api/tendik/pengajuan/{$pengajuanId}/finalisasi", [
            'nilai_per_mk' => [
                ['mk_id' => $mkId, 'huruf_nilai' => $snapshotNilai],
            ],
            'link_sk_konversi' => 'bukan-url-valid', // invalid
        ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['link_sk_konversi']);
    }

    // ─────────────────────────────────────────────────────────────
    // T9.2.8 — huruf_nilai tersimpan per MK di pengajuan_mata_kuliah
    // ─────────────────────────────────────────────────────────────
    public function test_huruf_nilai_tersimpan_per_mk_di_database(): void
    {
        [$pengajuanId, $mkId, $snapshotNilai] = $this->buatPengajuanDiterima();

        Sanctum::actingAs($this->tendik);
        $this->postJson("/api/tendik/pengajuan/{$pengajuanId}/finalisasi", [
            'nilai_per_mk' => [
                ['mk_id' => $mkId, 'huruf_nilai' => $snapshotNilai],
            ],
        ])->assertStatus(200);

        // Verifikasi huruf_nilai terisi di tabel pengajuan_mata_kuliah
        $this->assertDatabaseHas('pengajuan_mata_kuliah', [
            'pengajuan_id'   => $pengajuanId,
            'mata_kuliah_id' => $mkId,
            'huruf_nilai'    => $snapshotNilai,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // T9.2.9 — status berubah ke 'selesai' setelah finalisasi
    // ─────────────────────────────────────────────────────────────
    public function test_status_pengajuan_berubah_ke_selesai(): void
    {
        [$pengajuanId, $mkId, $snapshotNilai] = $this->buatPengajuanDiterima();

        Sanctum::actingAs($this->tendik);
        $this->postJson("/api/tendik/pengajuan/{$pengajuanId}/finalisasi", [
            'nilai_per_mk' => [
                ['mk_id' => $mkId, 'huruf_nilai' => $snapshotNilai],
            ],
        ])->assertStatus(200);

        $pengajuan = Pengajuan::find($pengajuanId);
        $this->assertEquals('selesai', $pengajuan->status);
        $this->assertNotNull($pengajuan->tendik_id);
        $this->assertNotNull($pengajuan->processed_at);
        $this->assertEquals($this->tendik->id, $pengajuan->tendik_id);
    }

    // ─────────────────────────────────────────────────────────────
    // T9.2.10 — mahasiswa bisa lihat hasil setelah status 'selesai'
    // ─────────────────────────────────────────────────────────────
    public function test_mahasiswa_bisa_lihat_hasil_setelah_selesai(): void
    {
        [$pengajuanId, $mkId, $snapshotNilai] = $this->buatPengajuanDiterima();

        // Tendik finalisasi
        Sanctum::actingAs($this->tendik);
        $this->postJson("/api/tendik/pengajuan/{$pengajuanId}/finalisasi", [
            'nilai_per_mk' => [
                ['mk_id' => $mkId, 'huruf_nilai' => $snapshotNilai],
            ],
        ])->assertStatus(200);

        // Mahasiswa lihat detail pengajuan miliknya
        Sanctum::actingAs($this->mhsSI);
        $res = $this->getJson("/api/mahasiswa/pengajuan/{$pengajuanId}");

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'selesai');

        // huruf_nilai di pengajuan_mata_kuliahs sudah terisi
        $pmks = $res->json('data.pengajuan_mata_kuliahs');
        $this->assertNotEmpty($pmks);
        foreach ($pmks as $pmk) {
            $this->assertEquals($snapshotNilai, $pmk['huruf_nilai']);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // T9.2.11 — non-tendik tidak bisa akses endpoint tendik
    // ─────────────────────────────────────────────────────────────
    public function test_non_tendik_tidak_bisa_akses_endpoint_tendik(): void
    {
        // Mahasiswa tidak bisa akses
        Sanctum::actingAs($this->mhsSI);
        $this->getJson('/api/tendik/pengajuan')
            ->assertStatus(403);

        // Verifikator tidak bisa akses
        Sanctum::actingAs($this->verifSI);
        $this->getJson('/api/tendik/pengajuan')
            ->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────
    // T9.2.12 — finalisasi pengajuan yang bukan 'diterima' → 422
    // ─────────────────────────────────────────────────────────────
    public function test_finalisasi_pengajuan_pending_return_422(): void
    {
        // Submit tapi TIDAK diterima (masih pending)
        Sanctum::actingAs($this->mhsSI);

        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mk     = \App\Models\BidangMataKuliah::where('bidang_id', $bidang->id)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $this->mhsSI->prodi_id))
            ->with('mataKuliah')->first()->mataKuliah;

        $res = $this->postJson('/api/mahasiswa/pengajuan', [
            'no_whatsapp'                  => '081234567890',
            'semester'                     => 6,
            'nama_lomba'                   => 'Lomba Pending Test',
            'bidang_id'                    => $bidang->id,
            'tingkatan_id'                 => 1,
            'tahapan_id'                   => 2,
            'mata_kuliah_ids'              => [$mk->id],
            'link_sertifikat'              => 'https://drive.google.com/sertifikat.pdf',
            'link_surat_tugas_mahasiswa'   => 'https://drive.google.com/st-mhs.pdf',
            'link_surat_tugas_dosen'       => 'https://drive.google.com/st-dsn.pdf',
            'link_poster'                  => 'https://drive.google.com/poster.jpg',
            'link_sosmed'                  => 'https://instagram.com/p/prestasi',
            'status_surat_tugas_mahasiswa' => 1,
            'status_surat_tugas_dosen'     => 1,
        ]);
        $pengajuanId = $res->json('data.id');

        // Tendik coba finalisasi pengajuan yang masih 'pending' → 422
        Sanctum::actingAs($this->tendik);
        $resFinalisasi = $this->postJson("/api/tendik/pengajuan/{$pengajuanId}/finalisasi", [
            'nilai_per_mk' => [
                ['mk_id' => $mk->id, 'huruf_nilai' => 'AB'],
            ],
        ]);

        $resFinalisasi->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }
}
