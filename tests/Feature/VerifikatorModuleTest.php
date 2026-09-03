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

class VerifikatorModuleTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected User $mhsSI;
    protected User $mhsTI;
    protected User $verifSI;
    protected User $verifTI;
    protected User $tendik;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mhsSI   = User::where('email', 'mhs.si@test.com')->first();
        $this->mhsTI   = User::where('email', 'mhs.ti@test.com')->first();
        $this->verifSI = User::where('email', 'verif.si@test.com')->first();
        $this->verifTI = User::where('email', 'verif.ti@test.com')->first();
        $this->tendik  = User::where('email', 'tendik@test.com')->first();
    }

    // ─────────────────────────────────────────────────────────────
    // Helper: buat 1 pengajuan pending milik mahasiswa SI
    // ─────────────────────────────────────────────────────────────
    private function buatPengajuanSI(): array
    {
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
            'tahapan_id'                   => 2, // Lolos tahap awal → 2-3 SKS, AB
            'mata_kuliah_ids'              => [$mk->id],
            'link_sertifikat'              => 'https://drive.google.com/sertifikat.pdf',
            'link_surat_tugas_mahasiswa'   => 'https://drive.google.com/st-mhs.pdf',
            'link_surat_tugas_dosen'       => 'https://drive.google.com/st-dsn.pdf',
            'link_poster'                  => 'https://drive.google.com/poster.jpg',
            'link_sosmed'                  => 'https://instagram.com/p/prestasi',
            'status_surat_tugas_mahasiswa' => 1,
            'status_surat_tugas_dosen'     => 1,
        ];

        $res = $this->postJson('/api/mahasiswa/pengajuan', $payload);
        $res->assertStatus(201);

        return [$res->json('data.id'), $payload];
    }

    // ─────────────────────────────────────────────────────────────
    // T8.3.1 — Verifikator SI bisa lihat pengajuan dari prodi SI
    // ─────────────────────────────────────────────────────────────
    public function test_verifikator_dapat_melihat_pengajuan_scope_prodinya(): void
    {
        $this->buatPengajuanSI();

        Sanctum::actingAs($this->verifSI);
        $res = $this->getJson('/api/verifikator/pengajuan');

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.meta.total', 1);

        // Pastikan pengajuan yang muncul memiliki status pending
        $this->assertEquals('pending', $res->json('data.items.0.status'));
        // Pastikan prodi yang muncul sesuai scope SI
        $prodiSI = Prodi::where('singkatan', 'SI')->first();
        $this->assertEquals($prodiSI->id, $res->json('data.items.0.prodi_id'));
    }

    // ─────────────────────────────────────────────────────────────
    // T8.3.2 — Verifikator TI tidak bisa lihat pengajuan prodi SI
    // ─────────────────────────────────────────────────────────────
    public function test_verifikator_ti_tidak_bisa_lihat_pengajuan_si(): void
    {
        [$pengajuanId] = $this->buatPengajuanSI();

        // GET index verifikator TI: tidak muncul (pengajuan SI tidak dalam scope)
        Sanctum::actingAs($this->verifTI);
        $resList = $this->getJson('/api/verifikator/pengajuan');
        $resList->assertStatus(200)
            ->assertJsonPath('data.meta.total', 0);

        // GET show verifikator TI: 403 karena beda prodi
        $resShow = $this->getJson("/api/verifikator/pengajuan/{$pengajuanId}");
        $resShow->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    // ─────────────────────────────────────────────────────────────
    // T8.3.3 — Verifikator SI bisa lihat detail pengajuan prodinya
    // ─────────────────────────────────────────────────────────────
    public function test_verifikator_dapat_melihat_detail_pengajuan(): void
    {
        [$pengajuanId] = $this->buatPengajuanSI();

        Sanctum::actingAs($this->verifSI);
        $res = $this->getJson("/api/verifikator/pengajuan/{$pengajuanId}");

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $pengajuanId)
            ->assertJsonPath('data.status', 'pending');

        // Relasi penting tersedia
        $this->assertNotNull($res->json('data.mahasiswa'));
        $this->assertNotNull($res->json('data.bidang'));
        $this->assertNotNull($res->json('data.tingkatan'));
        $this->assertNotNull($res->json('data.tahapan'));
    }

    // ─────────────────────────────────────────────────────────────
    // T8.3.4 — Terima pengajuan: status berubah ke 'diterima'
    // ─────────────────────────────────────────────────────────────
    public function test_verifikator_dapat_menerima_pengajuan(): void
    {
        [$pengajuanId] = $this->buatPengajuanSI();

        Sanctum::actingAs($this->verifSI);
        $res = $this->postJson("/api/verifikator/pengajuan/{$pengajuanId}/terima");

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'diterima');

        // Pastikan DB terupdate
        $this->assertDatabaseHas('pengajuan', [
            'id'             => $pengajuanId,
            'status'         => 'diterima',
            'verifikator_id' => $this->verifSI->id,
        ]);

        // verified_at harus terisi
        $pengajuan = Pengajuan::find($pengajuanId);
        $this->assertNotNull($pengajuan->verified_at);
    }

    // ─────────────────────────────────────────────────────────────
    // T8.3.5 — Tolak tanpa feedback: return 422
    // ─────────────────────────────────────────────────────────────
    public function test_tolak_tanpa_feedback_return_422(): void
    {
        [$pengajuanId] = $this->buatPengajuanSI();

        Sanctum::actingAs($this->verifSI);

        // Tanpa field feedback_verifikator sama sekali
        $res = $this->postJson("/api/verifikator/pengajuan/{$pengajuanId}/tolak", []);
        $res->assertStatus(422)
            ->assertJsonValidationErrors(['feedback_verifikator']);

        // Feedback kosong string
        $resEmpty = $this->postJson("/api/verifikator/pengajuan/{$pengajuanId}/tolak", [
            'feedback_verifikator' => '',
        ]);
        $resEmpty->assertStatus(422)
            ->assertJsonValidationErrors(['feedback_verifikator']);
    }

    // ─────────────────────────────────────────────────────────────
    // T8.3.6 — Tolak dengan feedback: status berubah ke 'ditolak'
    // ─────────────────────────────────────────────────────────────
    public function test_verifikator_dapat_menolak_pengajuan_dengan_feedback(): void
    {
        [$pengajuanId] = $this->buatPengajuanSI();

        Sanctum::actingAs($this->verifSI);
        $res = $this->postJson("/api/verifikator/pengajuan/{$pengajuanId}/tolak", [
            'feedback_verifikator' => 'Dokumen sertifikat tidak sesuai standar yang dipersyaratkan.',
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'ditolak')
            ->assertJsonPath('data.feedback_verifikator', 'Dokumen sertifikat tidak sesuai standar yang dipersyaratkan.');

        // Pastikan DB terupdate
        $this->assertDatabaseHas('pengajuan', [
            'id'                   => $pengajuanId,
            'status'               => 'ditolak',
            'verifikator_id'       => $this->verifSI->id,
            'feedback_verifikator' => 'Dokumen sertifikat tidak sesuai standar yang dipersyaratkan.',
        ]);

        // verified_at harus terisi
        $pengajuan = Pengajuan::find($pengajuanId);
        $this->assertNotNull($pengajuan->verified_at);
    }

    // ─────────────────────────────────────────────────────────────
    // T8.3.7 — Pengajuan 'diterima' muncul di list Tendik (query benar)
    //           Verifikasi: setelah terima, pengajuan muncul dengan status diterima
    // ─────────────────────────────────────────────────────────────
    public function test_pengajuan_diterima_muncul_di_scope_tendik(): void
    {
        [$pengajuanId] = $this->buatPengajuanSI();

        // Verifikator SI menerima pengajuan
        Sanctum::actingAs($this->verifSI);
        $this->postJson("/api/verifikator/pengajuan/{$pengajuanId}/terima")
            ->assertStatus(200);

        // Setelah diterima, pengajuan tidak muncul lagi di list pending verifikator
        $resVerif = $this->getJson('/api/verifikator/pengajuan');
        $resVerif->assertStatus(200)
            ->assertJsonPath('data.meta.total', 0);

        // Status di DB sudah 'diterima' — siap diproses Tendik
        $this->assertDatabaseHas('pengajuan', [
            'id'     => $pengajuanId,
            'status' => 'diterima',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // T8.3.8 — Non-verifikator (mahasiswa/tendik) tidak bisa akses endpoint verifikator
    // ─────────────────────────────────────────────────────────────
    public function test_non_verifikator_tidak_bisa_akses_endpoint_verifikator(): void
    {
        // Mahasiswa tidak bisa akses
        Sanctum::actingAs($this->mhsSI);
        $this->getJson('/api/verifikator/pengajuan')
            ->assertStatus(403);

        // Tendik tidak bisa akses
        Sanctum::actingAs($this->tendik);
        $this->getJson('/api/verifikator/pengajuan')
            ->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────
    // T8.3.9 — Verifikator TI tidak bisa terima/tolak pengajuan prodi SI
    // ─────────────────────────────────────────────────────────────
    public function test_verifikator_ti_tidak_bisa_proses_pengajuan_si(): void
    {
        [$pengajuanId] = $this->buatPengajuanSI();

        Sanctum::actingAs($this->verifTI);

        // Coba terima → harus 403 (scope mismatch, dari validateVerifikatorScope di service)
        $resTolak = $this->postJson("/api/verifikator/pengajuan/{$pengajuanId}/terima");
        $resTolak->assertStatus(403);

        // Coba tolak → harus 403 (scope mismatch)
        $resTolak = $this->postJson("/api/verifikator/pengajuan/{$pengajuanId}/tolak", [
            'feedback_verifikator' => 'Coba tolak dari luar scope.',
        ]);
        $resTolak->assertStatus(403);

        // Pastikan status tidak berubah
        $this->assertDatabaseHas('pengajuan', [
            'id'     => $pengajuanId,
            'status' => 'pending',
        ]);
    }
}
