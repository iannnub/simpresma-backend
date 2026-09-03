<?php

namespace Tests\Feature;

use App\Models\BidangLomba;
use App\Models\MataKuliah;
use App\Models\Pengajuan;
use App\Models\Prodi;
use App\Models\User;
use App\Models\VerifikatorProdi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SharedModuleTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected User $mhsSI;
    protected User $mhsTI;
    protected User $verifSI;
    protected User $verifTI;
    protected User $tendik;
    protected User $wadek;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mhsSI   = User::where('email', 'mhs.si@test.com')->first();
        $this->mhsTI   = User::where('email', 'mhs.ti@test.com')->first();
        $this->verifSI = User::where('email', 'verif.si@test.com')->first();
        $this->verifTI = User::where('email', 'verif.ti@test.com')->first();
        $this->tendik  = User::where('email', 'tendik@test.com')->first();
        $this->wadek   = User::where('email', 'wadek@test.com')->first();
    }

    // ─────────────────────────────────────────────────────────────
    // Helper: submit pengajuan dari satu user (wrapper)
    // ─────────────────────────────────────────────────────────────
    private function submitPengajuan(User $mahasiswa, string $namaLomba): int
    {
        Sanctum::actingAs($mahasiswa);

        $bidang = BidangLomba::where('nama', 'Programming')->first();

        // Ambil MK yang sudah ter-mapping ke bidang Programming milik prodi mahasiswa ini
        $mk = \App\Models\BidangMataKuliah::where('bidang_id', $bidang->id)
            ->where('is_active', 1)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $mahasiswa->prodi_id)->where('is_active', 1))
            ->first()
            ?->mataKuliah;

        $this->assertNotNull($mk, "Tidak ada MK prodi {$mahasiswa->prodi_id} yang mapped ke bidang Programming");

        $res = $this->postJson('/api/mahasiswa/pengajuan', [
            'no_whatsapp'                  => '081234567890',
            'semester'                     => 6,
            'nama_lomba'                   => $namaLomba,
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

        $res->assertStatus(201);
        return $res->json('data.id');
    }

    // ═══════════════════════════════════════════════════════════════
    // T11.1 — DASHBOARD STATISTIK
    // ═══════════════════════════════════════════════════════════════

    // ─────────────────────────────────────────────────────────────
    // T11.3.1 — Statistik: grand_total 0 ketika belum ada pengajuan
    // ─────────────────────────────────────────────────────────────
    public function test_statistik_kosong_ketika_belum_ada_pengajuan(): void
    {
        Sanctum::actingAs($this->wadek);
        $res = $this->getJson('/api/dashboard/statistik');

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.grand_total', 0);

        // Semua 3 prodi tetap muncul dengan total 0
        $perProdi = $res->json('data.per_prodi');
        $this->assertCount(3, $perProdi);
        foreach ($perProdi as $item) {
            $this->assertEquals(0, $item['total']);
            $this->assertEquals(0.0, $item['persentase']);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // T11.3.2 — Statistik: persentase dihitung benar sesuai distribusi data
    // ─────────────────────────────────────────────────────────────
    public function test_statistik_persentase_dihitung_benar(): void
    {
        // Submit 2 pengajuan SI dan 1 pengajuan TI
        $this->submitPengajuan($this->mhsSI, 'Lomba SI 1');

        // Mahasiswa SI submit kedua (nama lomba beda, pakai bidang UI/UX)
        Sanctum::actingAs($this->mhsSI);
        $bidangUIUX = BidangLomba::where('nama', 'UI/UX')->first();
        $mkUIUX = \App\Models\BidangMataKuliah::where('bidang_id', $bidangUIUX->id)
            ->where('is_active', 1)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $this->mhsSI->prodi_id)->where('is_active', 1))
            ->first()
            ?->mataKuliah;

        $this->submitPengajuan($this->mhsSI, 'Lomba SI 2');

        $this->submitPengajuan($this->mhsTI, 'Lomba TI 1');

        // Grand total = 3, SI = 2 (66.67%), TI = 1 (33.33%), IF = 0 (0%)
        Sanctum::actingAs($this->wadek);
        $res = $this->getJson('/api/dashboard/statistik');

        $res->assertStatus(200)
            ->assertJsonPath('data.grand_total', 3);

        $perProdi = collect($res->json('data.per_prodi'))->keyBy('prodi');

        // Prodi SI: 2 pengajuan, 66.67%
        $this->assertEquals(2, $perProdi['SI']['total']);
        $this->assertEquals(66.67, $perProdi['SI']['persentase']);

        // Prodi TI: 1 pengajuan, 33.33%
        $this->assertEquals(1, $perProdi['TI']['total']);
        $this->assertEquals(33.33, $perProdi['TI']['persentase']);

        // Prodi IF: 0 pengajuan, 0%
        $this->assertEquals(0, $perProdi['IF']['total']);
        $this->assertEquals(0.0, $perProdi['IF']['persentase']);

        // Jumlah persentase harus ~100 (toleransi rounding)
        $totalPersen = array_sum(array_column($res->json('data.per_prodi'), 'persentase'));
        $this->assertEqualsWithDelta(100.0, $totalPersen, 0.02);
    }

    // ─────────────────────────────────────────────────────────────
    // T11.3.3 — Statistik: breakdown by_status benar
    // ─────────────────────────────────────────────────────────────
    public function test_statistik_breakdown_status_benar(): void
    {
        // Submit 1 pengajuan SI → pending
        $pengajuanId = $this->submitPengajuan($this->mhsSI, 'Lomba SI Status Test');

        // Verifikator terima pengajuan → diterima
        Sanctum::actingAs($this->verifSI);
        $this->postJson("/api/verifikator/pengajuan/{$pengajuanId}/terima")->assertStatus(200);

        Sanctum::actingAs($this->wadek);
        $res = $this->getJson('/api/dashboard/statistik');
        $res->assertStatus(200);

        $perProdi = collect($res->json('data.per_prodi'))->keyBy('prodi');

        // SI: 1 pengajuan berstatus 'diterima'
        $this->assertEquals(0, $perProdi['SI']['by_status']['pending']);
        $this->assertEquals(1, $perProdi['SI']['by_status']['diterima']);
        $this->assertEquals(0, $perProdi['SI']['by_status']['ditolak']);
        $this->assertEquals(0, $perProdi['SI']['by_status']['selesai']);
    }

    // ─────────────────────────────────────────────────────────────
    // T11.3.4 — Statistik: semua role bisa akses (mahasiswa, verifikator, tendik, wadek)
    // ─────────────────────────────────────────────────────────────
    public function test_statistik_dapat_diakses_semua_role(): void
    {
        // Mahasiswa
        Sanctum::actingAs($this->mhsSI);
        $this->getJson('/api/dashboard/statistik')->assertStatus(200);

        // Verifikator
        Sanctum::actingAs($this->verifSI);
        $this->getJson('/api/dashboard/statistik')->assertStatus(200);

        // Tendik
        Sanctum::actingAs($this->tendik);
        $this->getJson('/api/dashboard/statistik')->assertStatus(200);

        // Wadek
        Sanctum::actingAs($this->wadek);
        $this->getJson('/api/dashboard/statistik')->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────────
    // T11.3.5 — Statistik: tanpa token → 401
    // ─────────────────────────────────────────────────────────────
    public function test_statistik_tanpa_auth_return_401(): void
    {
        $this->getJson('/api/dashboard/statistik')->assertStatus(401);
    }

    // ═══════════════════════════════════════════════════════════════
    // T11.2 — DIREKTORI VERIFIKATOR
    // ═══════════════════════════════════════════════════════════════

    // ─────────────────────────────────────────────────────────────
    // T11.3.6 — Direktori: semua prodi muncul (termasuk prodi dengan 0 verifikator)
    // ─────────────────────────────────────────────────────────────
    public function test_direktori_semua_prodi_muncul(): void
    {
        Sanctum::actingAs($this->mhsSI);
        $res = $this->getJson('/api/direktori-verifikator');

        $res->assertStatus(200)
            ->assertJsonPath('success', true);

        // Harus ada 3 prodi (SI, TI, IF)
        $this->assertCount(3, $res->json('data'));
    }

    // ─────────────────────────────────────────────────────────────
    // T11.3.7 — Direktori: verifikator aktif muncul + data lengkap
    // ─────────────────────────────────────────────────────────────
    public function test_direktori_verifikator_aktif_muncul_dengan_data_lengkap(): void
    {
        Sanctum::actingAs($this->wadek);
        $res = $this->getJson('/api/direktori-verifikator');

        $res->assertStatus(200);

        $data    = collect($res->json('data'))->keyBy('prodi');
        $prodiSI = $data['SI'];

        // Prodi SI harus punya minimal 1 verifikator (dari seeder)
        $this->assertGreaterThanOrEqual(1, $prodiSI['jumlah']);

        // Cek field yang harus ada di setiap verifikator
        $firstVerif = $prodiSI['verifikators'][0];
        $this->assertArrayHasKey('id', $firstVerif);
        $this->assertArrayHasKey('user_id', $firstVerif);
        $this->assertArrayHasKey('nama', $firstVerif);
        $this->assertArrayHasKey('nim_nip', $firstVerif);
        $this->assertArrayHasKey('email', $firstVerif);
        $this->assertArrayHasKey('no_whatsapp', $firstVerif);
    }

    // ─────────────────────────────────────────────────────────────
    // T11.3.8 — Direktori: setelah verifikator dicabut, tidak muncul lagi
    // ─────────────────────────────────────────────────────────────
    public function test_direktori_verifikator_dicabut_tidak_muncul(): void
    {
        $prodiSI = Prodi::where('singkatan', 'SI')->first();

        // Cabut verifSI dari prodi SI (lewat Wadek)
        Sanctum::actingAs($this->wadek);
        $vp = VerifikatorProdi::where('user_id', $this->verifSI->id)
            ->where('prodi_id', $prodiSI->id)
            ->where('is_active', 1)
            ->first();
        $this->deleteJson("/api/wadek/verifikator/{$vp->id}")->assertStatus(200);

        // Cek direktori: verifSI tidak muncul di prodi SI
        Sanctum::actingAs($this->mhsSI);
        $res = $this->getJson('/api/direktori-verifikator');
        $res->assertStatus(200);

        $data    = collect($res->json('data'))->keyBy('prodi');
        $verifIds = collect($data['SI']['verifikators'])->pluck('user_id')->toArray();

        $this->assertNotContains($this->verifSI->id, $verifIds);
    }

    // ─────────────────────────────────────────────────────────────
    // T11.3.9 — Direktori: semua role bisa akses
    // ─────────────────────────────────────────────────────────────
    public function test_direktori_dapat_diakses_semua_role(): void
    {
        Sanctum::actingAs($this->mhsSI);
        $this->getJson('/api/direktori-verifikator')->assertStatus(200);

        Sanctum::actingAs($this->verifSI);
        $this->getJson('/api/direktori-verifikator')->assertStatus(200);

        Sanctum::actingAs($this->tendik);
        $this->getJson('/api/direktori-verifikator')->assertStatus(200);

        Sanctum::actingAs($this->wadek);
        $this->getJson('/api/direktori-verifikator')->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────────
    // T11.3.10 — Direktori: tanpa token → 401
    // ─────────────────────────────────────────────────────────────
    public function test_direktori_tanpa_auth_return_401(): void
    {
        $this->getJson('/api/direktori-verifikator')->assertStatus(401);
    }

    // ─────────────────────────────────────────────────────────────
    // T11.3.11 — Direktori: setelah assign verifikator baru, langsung muncul
    // ─────────────────────────────────────────────────────────────
    public function test_direktori_verifikator_baru_langsung_muncul(): void
    {
        $prodiIF = Prodi::where('singkatan', 'IF')->first();

        // Cek jumlah verifikator awal di prodi IF (dari seeder)
        Sanctum::actingAs($this->wadek);
        $resBefore = $this->getJson('/api/direktori-verifikator');
        $dataBefore = collect($resBefore->json('data'))->keyBy('prodi');
        $jumlahSebelum = $dataBefore['IF']['jumlah'];

        // Assign tendik ke prodi IF sebagai verifikator baru
        $this->postJson('/api/wadek/verifikator', [
            'user_id'  => $this->tendik->id,
            'prodi_id' => $prodiIF->id,
        ])->assertStatus(201);

        // Cek direktori: verifikator baru langsung muncul di prodi IF
        Sanctum::actingAs($this->mhsSI);
        $resAfter = $this->getJson('/api/direktori-verifikator');
        $dataAfter = collect($resAfter->json('data'))->keyBy('prodi');

        $this->assertEquals($jumlahSebelum + 1, $dataAfter['IF']['jumlah']);

        $verifIds = collect($dataAfter['IF']['verifikators'])->pluck('user_id')->toArray();
        $this->assertContains($this->tendik->id, $verifIds);
    }
}
