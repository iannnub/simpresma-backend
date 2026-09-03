<?php

namespace Tests\Feature;

use App\Models\BidangLomba;
use App\Models\BidangMataKuliah;
use App\Models\MatriksKonversi;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\User;
use App\Models\VerifikatorProdi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WadekModuleTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected User $wadek;
    protected User $mhsSI;
    protected User $verifSI;
    protected User $tendik;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wadek   = User::where('email', 'wadek@test.com')->first();
        $this->mhsSI   = User::where('email', 'mhs.si@test.com')->first();
        $this->verifSI = User::where('email', 'verif.si@test.com')->first();
        $this->tendik  = User::where('email', 'tendik@test.com')->first();
    }

    // ═══════════════════════════════════════════════════════════════
    // T10.1 — MATRIKS KONVERSI
    // ═══════════════════════════════════════════════════════════════

    // ─────────────────────────────────────────────────────────────
    // T10.4.1 — List matriks: 24 baris, relasi tingkatan+tahapan
    // ─────────────────────────────────────────────────────────────
    public function test_wadek_dapat_list_semua_matriks(): void
    {
        Sanctum::actingAs($this->wadek);
        $res = $this->getJson('/api/wadek/matriks');

        $res->assertStatus(200)
            ->assertJsonPath('success', true);

        // Seeder seed 24 baris matriks
        $this->assertCount(24, $res->json('data'));

        // Setiap baris punya relasi tingkatan dan tahapan
        $first = $res->json('data.0');
        $this->assertArrayHasKey('tingkatan', $first);
        $this->assertArrayHasKey('tahapan', $first);
    }

    // ─────────────────────────────────────────────────────────────
    // T10.4.2 — Update matriks: nilai tersimpan + updated_by terisi
    // ─────────────────────────────────────────────────────────────
    public function test_wadek_dapat_update_matriks(): void
    {
        Sanctum::actingAs($this->wadek);

        // Ambil matriks tingkatan=1, tahapan=2 (Internasional, Lolos tahap awal → min=2, max=3, AB)
        $matriks = MatriksKonversi::where('tingkatan_id', 1)->where('tahapan_id', 2)->first();

        $res = $this->putJson("/api/wadek/matriks/{$matriks->id}", [
            'min_sks'     => 3,
            'max_sks'     => 4,
            'huruf_nilai' => 'A',
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.min_sks', 3)
            ->assertJsonPath('data.max_sks', 4)
            ->assertJsonPath('data.huruf_nilai', 'A');

        // updated_by harus terisi dengan ID wadek
        $this->assertDatabaseHas('matriks_konversi', [
            'id'         => $matriks->id,
            'min_sks'    => 3,
            'max_sks'    => 4,
            'huruf_nilai'=> 'A',
            'updated_by' => $this->wadek->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // T10.4.3 — Update matriks: max_sks < min_sks → 422
    // ─────────────────────────────────────────────────────────────
    public function test_update_matriks_max_sks_lebih_kecil_dari_min_sks_return_422(): void
    {
        Sanctum::actingAs($this->wadek);

        $matriks = MatriksKonversi::first();

        $res = $this->putJson("/api/wadek/matriks/{$matriks->id}", [
            'min_sks' => 5,
            'max_sks' => 3, // < min_sks
        ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['max_sks']);
    }

    // ─────────────────────────────────────────────────────────────
    // T10.4.4 — Pengajuan baru setelah matriks diupdate → pakai matriks baru (snapshot saat submit)
    // ─────────────────────────────────────────────────────────────
    public function test_pengajuan_baru_pakai_matriks_baru_setelah_update(): void
    {
        Sanctum::actingAs($this->wadek);

        // Update matriks tingkatan=1, tahapan=2 ke nilai baru
        $matriks = MatriksKonversi::where('tingkatan_id', 1)->where('tahapan_id', 2)->first();
        $this->putJson("/api/wadek/matriks/{$matriks->id}", [
            'min_sks'     => 2,
            'max_sks'     => 3,
            'huruf_nilai' => 'B', // Diubah dari 'AB' ke 'B'
        ])->assertStatus(200);

        // Mahasiswa SI submit pengajuan baru → snapshot harus 'B'
        Sanctum::actingAs($this->mhsSI);

        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mk     = \App\Models\BidangMataKuliah::where('bidang_id', $bidang->id)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $this->mhsSI->prodi_id))
            ->with('mataKuliah')->first()->mataKuliah;

        $res = $this->postJson('/api/mahasiswa/pengajuan', [
            'no_whatsapp'                  => '081234567890',
            'semester'                     => 6,
            'nama_lomba'                   => 'Lomba Setelah Update Matriks',
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

        $res->assertStatus(201)
            ->assertJsonPath('data.snapshot_huruf_nilai', 'B'); // Pakai matriks baru
    }

    // ─────────────────────────────────────────────────────────────
    // T10.4.5 — Pengajuan lama snapshot tidak berubah setelah matriks diupdate
    // ─────────────────────────────────────────────────────────────
    public function test_pengajuan_lama_snapshot_tidak_berubah_setelah_update_matriks(): void
    {
        // Submit pengajuan DULU (snapshot 'AB')
        Sanctum::actingAs($this->mhsSI);

        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mk     = \App\Models\BidangMataKuliah::where('bidang_id', $bidang->id)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $this->mhsSI->prodi_id))
            ->with('mataKuliah')->first()->mataKuliah;

        $resSubmit = $this->postJson('/api/mahasiswa/pengajuan', [
            'no_whatsapp'                  => '081234567890',
            'semester'                     => 6,
            'nama_lomba'                   => 'Lomba Lama Sebelum Update',
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
        $pengajuanId   = $resSubmit->json('data.id');
        $snapshotLama  = $resSubmit->json('data.snapshot_huruf_nilai'); // 'AB'

        // WADEK update matriks SESUDAHNYA
        Sanctum::actingAs($this->wadek);
        $matriks = MatriksKonversi::where('tingkatan_id', 1)->where('tahapan_id', 2)->first();
        $this->putJson("/api/wadek/matriks/{$matriks->id}", [
            'min_sks'     => 2,
            'max_sks'     => 3,
            'huruf_nilai' => 'C', // Diubah jadi 'C'
        ]);

        // Cek pengajuan LAMA: snapshot tetap 'AB', bukan 'C'
        $this->assertDatabaseHas('pengajuan', [
            'id'                   => $pengajuanId,
            'snapshot_huruf_nilai' => $snapshotLama, // tetap 'AB'
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // T10.2 — MANAJEMEN VERIFIKATOR
    // ═══════════════════════════════════════════════════════════════

    // ─────────────────────────────────────────────────────────────
    // T10.4.6 — List verifikator aktif
    // ─────────────────────────────────────────────────────────────
    public function test_wadek_dapat_list_verifikator_aktif(): void
    {
        Sanctum::actingAs($this->wadek);
        $res = $this->getJson('/api/wadek/verifikator');

        $res->assertStatus(200)
            ->assertJsonPath('success', true);

        // Seeder: 3 verifikator aktif (SI, TI, IF) + 1 multi-role SI (total 4)
        $this->assertGreaterThanOrEqual(3, count($res->json('data')));
    }

    // ─────────────────────────────────────────────────────────────
    // T10.4.7 — Assign verifikator baru ke prodi
    // ─────────────────────────────────────────────────────────────
    public function test_wadek_dapat_assign_verifikator_baru(): void
    {
        Sanctum::actingAs($this->wadek);

        // Gunakan user tendik (belum punya role verifikator) dan assign ke prodi IF
        $prodiIF = Prodi::where('singkatan', 'IF')->first();

        $res = $this->postJson('/api/wadek/verifikator', [
            'user_id'  => $this->tendik->id,
            'prodi_id' => $prodiIF->id,
        ]);

        $res->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user_id', $this->tendik->id)
            ->assertJsonPath('data.prodi_id', $prodiIF->id);

        // Record tersimpan di DB
        $this->assertDatabaseHas('verifikator_prodi', [
            'user_id'     => $this->tendik->id,
            'prodi_id'    => $prodiIF->id,
            'is_active'   => 1,
            'assigned_by' => $this->wadek->id,
        ]);

        // Role 'verifikator' otomatis ditambahkan
        $this->tendik->refresh();
        $this->assertTrue($this->tendik->hasRole('verifikator'));
    }

    // ─────────────────────────────────────────────────────────────
    // T10.4.8 — Cabut verifikator dari satu prodi, masih ada prodi lain → role dipertahankan
    // ─────────────────────────────────────────────────────────────
    public function test_cabut_verifikator_dengan_prodi_lain_aktif_role_dipertahankan(): void
    {
        Sanctum::actingAs($this->wadek);

        // Assign verifSI ke prodi TI juga (sekarang punya 2 prodi aktif: SI + TI)
        $prodiTI = Prodi::where('singkatan', 'TI')->first();
        $this->postJson('/api/wadek/verifikator', [
            'user_id'  => $this->verifSI->id,
            'prodi_id' => $prodiTI->id,
        ])->assertStatus(201);

        // Ambil record verifikator_prodi untuk verifSI di prodi SI
        $prodiSI = Prodi::where('singkatan', 'SI')->first();
        $vpSI    = VerifikatorProdi::where('user_id', $this->verifSI->id)
            ->where('prodi_id', $prodiSI->id)
            ->first();

        // Cabut dari prodi SI
        $res = $this->deleteJson("/api/wadek/verifikator/{$vpSI->id}");
        $res->assertStatus(200)->assertJsonPath('success', true);

        // is_active = 0 di prodi SI
        $this->assertDatabaseHas('verifikator_prodi', [
            'id'        => $vpSI->id,
            'is_active' => 0,
        ]);

        // Masih punya prodi TI aktif → role 'verifikator' TIDAK dicabut
        $this->verifSI->refresh();
        $this->assertTrue($this->verifSI->hasRole('verifikator'));
    }

    // ─────────────────────────────────────────────────────────────
    // T10.4.9 — Cabut verifikator dari semua prodi → role verifikator dicabut
    // ─────────────────────────────────────────────────────────────
    public function test_cabut_verifikator_dari_semua_prodi_role_dicabut(): void
    {
        Sanctum::actingAs($this->wadek);

        // verifSI hanya punya 1 prodi aktif (SI dari seeder)
        $prodiSI = Prodi::where('singkatan', 'SI')->first();
        $vp      = VerifikatorProdi::where('user_id', $this->verifSI->id)
            ->where('prodi_id', $prodiSI->id)
            ->where('is_active', 1)
            ->first();

        $res = $this->deleteJson("/api/wadek/verifikator/{$vp->id}");
        $res->assertStatus(200)->assertJsonPath('success', true);

        // is_active = 0
        $this->assertDatabaseHas('verifikator_prodi', [
            'id'        => $vp->id,
            'is_active' => 0,
        ]);

        // Tidak ada prodi aktif lain → role 'verifikator' dicabut
        $this->verifSI->refresh();
        $this->assertFalse($this->verifSI->hasRole('verifikator'));
    }

    // ═══════════════════════════════════════════════════════════════
    // T10.3 — BIDANG MATA KULIAH
    // ═══════════════════════════════════════════════════════════════

    // ─────────────────────────────────────────────────────────────
    // T10.4.10 — List mapping bidang → MK (dengan filter)
    // ─────────────────────────────────────────────────────────────
    public function test_wadek_dapat_list_mapping_bidang_mk(): void
    {
        Sanctum::actingAs($this->wadek);

        // List semua tanpa filter
        $res = $this->getJson('/api/wadek/bidang-mata-kuliah');
        $res->assertStatus(200)->assertJsonPath('success', true);
        $this->assertNotEmpty($res->json('data'));

        // Filter by bidang_id
        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $resFiltered = $this->getJson("/api/wadek/bidang-mata-kuliah?bidang_id={$bidang->id}");
        $resFiltered->assertStatus(200);
        foreach ($resFiltered->json('data') as $item) {
            $this->assertEquals($bidang->id, $item['bidang_id']);
        }

        // Filter by prodi_id (prodi SI)
        $prodiSI     = Prodi::where('singkatan', 'SI')->first();
        $resProdi    = $this->getJson("/api/wadek/bidang-mata-kuliah?prodi_id={$prodiSI->id}");
        $resProdi->assertStatus(200);
        $this->assertNotEmpty($resProdi->json('data'));
    }

    // ─────────────────────────────────────────────────────────────
    // T10.4.11 — Tambah mapping baru bidang → MK
    // ─────────────────────────────────────────────────────────────
    public function test_wadek_dapat_tambah_mapping_bidang_mk(): void
    {
        Sanctum::actingAs($this->wadek);

        // Cari bidang dan MK yang belum ada mappingnya
        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $prodiSI = Prodi::where('singkatan', 'SI')->first();

        // Ambil MK prodi SI yang belum ada di mapping bidang Programming
        $mkIdsSudahAda = BidangMataKuliah::where('bidang_id', $bidang->id)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $prodiSI->id))
            ->pluck('mata_kuliah_id')
            ->toArray();

        $mkBaru = MataKuliah::where('prodi_id', $prodiSI->id)
            ->whereNotIn('id', $mkIdsSudahAda)
            ->first();

        if (!$mkBaru) {
            $this->markTestSkipped('Tidak ada MK SI yang belum di-mapping ke bidang Programming.');
        }

        $res = $this->postJson('/api/wadek/bidang-mata-kuliah', [
            'bidang_id'      => $bidang->id,
            'mata_kuliah_id' => $mkBaru->id,
        ]);

        $res->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.bidang_id', $bidang->id)
            ->assertJsonPath('data.mata_kuliah_id', $mkBaru->id);

        $this->assertDatabaseHas('bidang_mata_kuliah', [
            'bidang_id'      => $bidang->id,
            'mata_kuliah_id' => $mkBaru->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // T10.4.12 — Tambah mapping duplikat → 422
    // ─────────────────────────────────────────────────────────────
    public function test_tambah_mapping_duplikat_return_422(): void
    {
        Sanctum::actingAs($this->wadek);

        // Ambil mapping yang sudah ada dari seeder
        $existingMapping = BidangMataKuliah::first();

        $res = $this->postJson('/api/wadek/bidang-mata-kuliah', [
            'bidang_id'      => $existingMapping->bidang_id,
            'mata_kuliah_id' => $existingMapping->mata_kuliah_id,
        ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['mata_kuliah_id']);
    }

    // ─────────────────────────────────────────────────────────────
    // T10.4.13 — Hapus mapping bidang → MK
    // ─────────────────────────────────────────────────────────────
    public function test_wadek_dapat_hapus_mapping_bidang_mk(): void
    {
        Sanctum::actingAs($this->wadek);

        // Buat mapping baru dulu lalu hapus
        $bidang  = BidangLomba::where('nama', 'Jaringan dan Sekuritas')->first();
        $prodiTI = Prodi::where('singkatan', 'TI')->first();

        // Ambil MK TI yang belum ada di mapping bidang Networking
        $mkIdsSudahAda = BidangMataKuliah::where('bidang_id', $bidang->id)->pluck('mata_kuliah_id')->toArray();
        $mk = MataKuliah::where('prodi_id', $prodiTI->id)->whereNotIn('id', $mkIdsSudahAda)->first();

        if (!$mk) {
            $this->markTestSkipped('Tidak ada MK TI yang bisa di-map ke Networking.');
        }

        // Tambah mapping
        $resStore = $this->postJson('/api/wadek/bidang-mata-kuliah', [
            'bidang_id'      => $bidang->id,
            'mata_kuliah_id' => $mk->id,
        ]);
        $resStore->assertStatus(201);
        $mappingId = $resStore->json('data.id');

        // Hapus mapping
        $resDelete = $this->deleteJson("/api/wadek/bidang-mata-kuliah/{$mappingId}");
        $resDelete->assertStatus(200)->assertJsonPath('success', true);

        // Pastikan sudah terhapus dari DB
        $this->assertDatabaseMissing('bidang_mata_kuliah', ['id' => $mappingId]);
    }

    // ─────────────────────────────────────────────────────────────
    // T10.4.14 — Non-wadek tidak bisa akses endpoint wadek
    // ─────────────────────────────────────────────────────────────
    public function test_non_wadek_tidak_bisa_akses_endpoint_wadek(): void
    {
        // Mahasiswa tidak bisa akses
        Sanctum::actingAs($this->mhsSI);
        $this->getJson('/api/wadek/matriks')->assertStatus(403);

        // Verifikator tidak bisa akses
        Sanctum::actingAs($this->verifSI);
        $this->getJson('/api/wadek/matriks')->assertStatus(403);

        // Tendik tidak bisa akses
        Sanctum::actingAs($this->tendik);
        $this->getJson('/api/wadek/matriks')->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────
    // T10.4.15 — Assign verifikator dengan user_id/prodi_id tidak valid → 422
    // ─────────────────────────────────────────────────────────────
    public function test_assign_verifikator_user_tidak_ada_return_422(): void
    {
        Sanctum::actingAs($this->wadek);

        $res = $this->postJson('/api/wadek/verifikator', [
            'user_id'  => 99999, // tidak ada
            'prodi_id' => 1,
        ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }
}
