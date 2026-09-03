<?php

namespace Tests\Feature;

use App\Models\BidangLomba;
use App\Models\BidangMataKuliah;
use App\Models\MataKuliah;
use App\Models\MatriksKonversi;
use App\Models\Pengajuan;
use App\Models\PengajuanMataKuliah;
use App\Models\Prodi;
use App\Models\User;
use App\Models\VerifikatorProdi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HardeningTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected User $mhsSI;
    protected User $mhsTI;
    protected User $mhsIF;
    protected User $verifSI;
    protected User $verifTI;
    protected User $tendik;
    protected User $wadek;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mhsSI   = User::where('email', 'mhs.si@test.com')->first();
        $this->mhsTI   = User::where('email', 'mhs.ti@test.com')->first();
        $this->mhsIF   = User::where('email', 'mhs.if@test.com')->first();
        $this->verifSI = User::where('email', 'verif.si@test.com')->first();
        $this->verifTI = User::where('email', 'verif.ti@test.com')->first();
        $this->tendik  = User::where('email', 'tendik@test.com')->first();
        $this->wadek   = User::where('email', 'wadek@test.com')->first();
    }

    /**
     * Helper: Dapatkan MK valid untuk mahasiswa berdasarkan bidang tertentu.
     */
    private function getValidMkForMahasiswa(User $mahasiswa, string $namaBidang): MataKuliah
    {
        $bidang = BidangLomba::where('nama', $namaBidang)->first();
        $mk = BidangMataKuliah::where('bidang_id', $bidang->id)
            ->where('is_active', 1)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $mahasiswa->prodi_id)->where('is_active', 1))
            ->first()
            ?->mataKuliah;

        $this->assertNotNull($mk, "Mata kuliah valid tidak ditemukan untuk prodi {$mahasiswa->prodi_id} pada bidang {$namaBidang}");
        return $mk;
    }

    /**
     * Helper: Submit pengajuan valid untuk mahasiswa.
     */
    private function submitValidPengajuan(User $mahasiswa, string $namaLomba = 'Lomba Hardening'): Pengajuan
    {
        Sanctum::actingAs($mahasiswa);

        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mk = $this->getValidMkForMahasiswa($mahasiswa, 'Programming');

        $res = $this->postJson('/api/mahasiswa/pengajuan', [
            'no_whatsapp'                  => '081234567890',
            'semester'                     => 6,
            'nama_lomba'                   => $namaLomba,
            'bidang_id'                    => $bidang->id,
            'tingkatan_id'                 => 1, // Internasional
            'tahapan_id'                   => 2, // Lolos tahap awal (2-3 SKS, AB)
            'mata_kuliah_ids'              => [$mk->id],
            'link_sertifikat'              => 'https://example.com/sertifikat.pdf',
            'link_surat_tugas_mahasiswa'   => 'https://example.com/st-mhs.pdf',
            'link_surat_tugas_dosen'       => 'https://example.com/st-dsn.pdf',
            'link_poster'                  => 'https://example.com/poster.jpg',
            'link_sosmed'                  => 'https://example.com/sosmed',
            'status_surat_tugas_mahasiswa' => 1,
            'status_surat_tugas_dosen'     => 1,
        ]);

        $res->assertStatus(201);
        return Pengajuan::find($res->json('data.id'));
    }

    // ═══════════════════════════════════════════════════════════════
    // T12.1 — SECURITY & EDGE CASES
    // ═══════════════════════════════════════════════════════════════

    /**
     * 1. Semua endpoint API yang dilindungi harus mengembalikan 401 saat diakses tanpa token.
     */
    public function test_unauthenticated_requests_return_401(): void
    {
        $protectedEndpoints = [
            ['GET', '/api/auth/me'],
            ['POST', '/api/auth/logout'],
            ['GET', '/api/ref/prodi'],
            ['GET', '/api/mahasiswa/pengajuan'],
            ['POST', '/api/mahasiswa/pengajuan'],
            ['GET', '/api/mahasiswa/pengajuan/1'],
            ['GET', '/api/verifikator/pengajuan'],
            ['GET', '/api/verifikator/pengajuan/1'],
            ['POST', '/api/verifikator/pengajuan/1/terima'],
            ['POST', '/api/verifikator/pengajuan/1/tolak'],
            ['GET', '/api/tendik/pengajuan'],
            ['GET', '/api/tendik/pengajuan/1'],
            ['POST', '/api/tendik/pengajuan/1/finalisasi'],
            ['GET', '/api/wadek/matriks'],
            ['PUT', '/api/wadek/matriks/1'],
            ['GET', '/api/wadek/verifikator'],
            ['POST', '/api/wadek/verifikator'],
            ['DELETE', '/api/wadek/verifikator/1'],
            ['GET', '/api/wadek/bidang-mata-kuliah'],
            ['POST', '/api/wadek/bidang-mata-kuliah'],
            ['DELETE', '/api/wadek/bidang-mata-kuliah/1'],
            ['GET', '/api/dashboard/statistik'],
            ['GET', '/api/direktori-verifikator'],
        ];

        foreach ($protectedEndpoints as [$method, $uri]) {
            $response = $this->json($method, $uri);
            $response->assertStatus(401)
                ->assertJsonPath('success', false);
        }
    }

    /**
     * 2. Isolasi Role: Mahasiswa tidak boleh mengakses endpoint Verifikator, Tendik, atau Wadek.
     */
    public function test_mahasiswa_cannot_access_other_role_endpoints(): void
    {
        Sanctum::actingAs($this->mhsSI);

        // Verifikator endpoints
        $this->getJson('/api/verifikator/pengajuan')->assertStatus(403);
        $this->getJson('/api/verifikator/pengajuan/1')->assertStatus(403);
        $this->postJson('/api/verifikator/pengajuan/1/terima')->assertStatus(403);
        $this->postJson('/api/verifikator/pengajuan/1/tolak', ['feedback_verifikator' => 'test'])->assertStatus(403);

        // Tendik endpoints
        $this->getJson('/api/tendik/pengajuan')->assertStatus(403);
        $this->getJson('/api/tendik/pengajuan/1')->assertStatus(403);
        $this->postJson('/api/tendik/pengajuan/1/finalisasi', ['nilai_per_mk' => []])->assertStatus(403);

        // Wadek endpoints
        $this->getJson('/api/wadek/matriks')->assertStatus(403);
        $this->putJson('/api/wadek/matriks/1', ['min_sks' => 2])->assertStatus(403);
        $this->getJson('/api/wadek/verifikator')->assertStatus(403);
        $this->postJson('/api/wadek/verifikator', ['user_id' => 1, 'prodi_id' => 1])->assertStatus(403);
        $this->deleteJson('/api/wadek/verifikator/1')->assertStatus(403);
        $this->getJson('/api/wadek/bidang-mata-kuliah')->assertStatus(403);
        $this->postJson('/api/wadek/bidang-mata-kuliah', ['bidang_id' => 1, 'mata_kuliah_id' => 1])->assertStatus(403);
        $this->deleteJson('/api/wadek/bidang-mata-kuliah/1')->assertStatus(403);
    }

    /**
     * 3. Isolasi Role: Verifikator tidak boleh mengakses endpoint Mahasiswa, Tendik, atau Wadek.
     */
    public function test_verifikator_cannot_access_other_role_endpoints(): void
    {
        Sanctum::actingAs($this->verifSI);

        // Mahasiswa endpoints
        $this->getJson('/api/mahasiswa/pengajuan')->assertStatus(403);
        $this->postJson('/api/mahasiswa/pengajuan', [])->assertStatus(403);
        $this->getJson('/api/mahasiswa/pengajuan/1')->assertStatus(403);

        // Tendik endpoints
        $this->getJson('/api/tendik/pengajuan')->assertStatus(403);
        $this->getJson('/api/tendik/pengajuan/1')->assertStatus(403);
        $this->postJson('/api/tendik/pengajuan/1/finalisasi', [])->assertStatus(403);

        // Wadek endpoints
        $this->getJson('/api/wadek/matriks')->assertStatus(403);
        $this->putJson('/api/wadek/matriks/1', [])->assertStatus(403);
        $this->getJson('/api/wadek/verifikator')->assertStatus(403);
        $this->postJson('/api/wadek/verifikator', [])->assertStatus(403);
    }

    /**
     * 4. Isolasi Role: Tendik tidak boleh mengakses endpoint Mahasiswa, Verifikator, atau Wadek.
     */
    public function test_tendik_cannot_access_other_role_endpoints(): void
    {
        Sanctum::actingAs($this->tendik);

        // Mahasiswa endpoints
        $this->getJson('/api/mahasiswa/pengajuan')->assertStatus(403);
        $this->postJson('/api/mahasiswa/pengajuan', [])->assertStatus(403);

        // Verifikator endpoints
        $this->getJson('/api/verifikator/pengajuan')->assertStatus(403);
        $this->postJson('/api/verifikator/pengajuan/1/terima')->assertStatus(403);

        // Wadek endpoints
        $this->getJson('/api/wadek/matriks')->assertStatus(403);
        $this->putJson('/api/wadek/matriks/1', [])->assertStatus(403);
    }

    /**
     * 5. Validasi URL: Semua field link dokumen harus berupa format URL yang valid.
     */
    public function test_url_fields_must_be_valid_urls(): void
    {
        Sanctum::actingAs($this->mhsSI);

        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mk = $this->getValidMkForMahasiswa($this->mhsSI, 'Programming');

        $basePayload = [
            'no_whatsapp'                  => '081234567890',
            'nama_lomba'                   => 'Test URL Validations',
            'bidang_id'                    => $bidang->id,
            'tingkatan_id'                 => 1,
            'tahapan_id'                   => 2,
            'mata_kuliah_ids'              => [$mk->id],
            'link_sertifikat'              => 'https://example.com/sertifikat.pdf',
            'status_surat_tugas_mahasiswa' => 0,
            'status_surat_tugas_dosen'     => 0,
        ];

        // 5a. link_sertifikat bukan URL valid
        $p1 = $basePayload;
        $p1['link_sertifikat'] = 'bukan_url_valid';
        $this->postJson('/api/mahasiswa/pengajuan', $p1)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['link_sertifikat']);

        // 5b. status_surat_tugas_mahasiswa = 1 tapi link bukan URL valid
        $p2 = $basePayload;
        $p2['status_surat_tugas_mahasiswa'] = 1;
        $p2['link_surat_tugas_mahasiswa'] = 'bukan-url-valid';
        $this->postJson('/api/mahasiswa/pengajuan', $p2)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['link_surat_tugas_mahasiswa']);

        // 5c. status_surat_tugas_dosen = 1 tapi link bukan URL valid
        $p3 = $basePayload;
        $p3['status_surat_tugas_dosen'] = 1;
        $p3['link_surat_tugas_dosen'] = 'bukan-url-valid';
        $this->postJson('/api/mahasiswa/pengajuan', $p3)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['link_surat_tugas_dosen']);

        // 5d. link_poster bukan URL valid
        $p4 = $basePayload;
        $p4['link_poster'] = 'invalid_poster';
        $this->postJson('/api/mahasiswa/pengajuan', $p4)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['link_poster']);

        // 5e. link_sosmed bukan URL valid
        $p5 = $basePayload;
        $p5['link_sosmed'] = 'invalid_sosmed';
        $this->postJson('/api/mahasiswa/pengajuan', $p5)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['link_sosmed']);
    }

    /**
     * 6. Validasi Mata Kuliah: Tidak boleh submit MK dari prodi lain.
     */
    public function test_cannot_submit_mata_kuliah_from_other_prodi(): void
    {
        Sanctum::actingAs($this->mhsSI);

        $bidang = BidangLomba::where('nama', 'Programming')->first();

        // Cari MK prodi TI yang ter-mapping ke bidang Programming
        $mkTI = BidangMataKuliah::where('bidang_id', $bidang->id)
            ->where('is_active', 1)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $this->mhsTI->prodi_id))
            ->first()
            ?->mataKuliah;

        $this->assertNotNull($mkTI);

        $this->postJson('/api/mahasiswa/pengajuan', [
            'no_whatsapp'                  => '081234567890',
            'semester'                     => 6,
            'nama_lomba'                   => 'Lomba Cross Prodi MK',
            'bidang_id'                    => $bidang->id,
            'tingkatan_id'                 => 1,
            'tahapan_id'                   => 2,
            'mata_kuliah_ids'              => [$mkTI->id], // MK milik prodi TI, mhs prodi SI
            'link_sertifikat'              => 'https://example.com/sertifikat.pdf',
            'link_surat_tugas_mahasiswa'   => 'https://example.com/st-mhs.pdf',
            'link_surat_tugas_dosen'       => 'https://example.com/st-dsn.pdf',
            'link_poster'                  => 'https://example.com/poster.jpg',
            'link_sosmed'                  => 'https://example.com/sosmed',
            'status_surat_tugas_mahasiswa' => 1,
            'status_surat_tugas_dosen'     => 1,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['mata_kuliah_ids']);
    }

    /**
     * 7. Validasi Mata Kuliah: Tidak boleh submit MK yang tidak terhubung dengan bidang lomba.
     */
    public function test_cannot_submit_mata_kuliah_from_wrong_bidang(): void
    {
        Sanctum::actingAs($this->mhsSI);

        $bidangProgramming = BidangLomba::where('nama', 'Programming')->first();

        // Cari MK SI yang hanya terhubung ke bidang selain Programming (misal UI/UX)
        $bidangUIUX = BidangLomba::where('nama', 'UI/UX')->first();
        $mkUIUX = BidangMataKuliah::where('bidang_id', $bidangUIUX->id)
            ->where('is_active', 1)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $this->mhsSI->prodi_id))
            ->whereNotIn('mata_kuliah_id', function ($query) use ($bidangProgramming) {
                $query->select('mata_kuliah_id')
                    ->from('bidang_mata_kuliah')
                    ->where('bidang_id', $bidangProgramming->id);
            })
            ->first()
            ?->mataKuliah;

        if ($mkUIUX) {
            $this->postJson('/api/mahasiswa/pengajuan', [
                'no_whatsapp'                  => '081234567890',
                'semester'                     => 6,
                'nama_lomba'                   => 'Lomba Wrong Bidang MK',
                'bidang_id'                    => $bidangProgramming->id, // Lomba Programming
                'tingkatan_id'                 => 1,
                'tahapan_id'                   => 2,
                'mata_kuliah_ids'              => [$mkUIUX->id], // MK bukan dari bidang Programming
                'link_sertifikat'              => 'https://example.com/sertifikat.pdf',
                'link_surat_tugas_mahasiswa'   => 'https://example.com/st-mhs.pdf',
                'link_surat_tugas_dosen'       => 'https://example.com/st-dsn.pdf',
                'link_poster'                  => 'https://example.com/poster.jpg',
                'link_sosmed'                  => 'https://example.com/sosmed',
                'status_surat_tugas_mahasiswa' => 1,
                'status_surat_tugas_dosen'     => 1,
            ])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['mata_kuliah_ids']);
        }
    }

    /**
     * 8. Validasi Duplikasi MK ID dalam input array.
     */
    public function test_cannot_submit_duplicate_mata_kuliah_ids(): void
    {
        Sanctum::actingAs($this->mhsSI);

        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mk = $this->getValidMkForMahasiswa($this->mhsSI, 'Programming');

        $this->postJson('/api/mahasiswa/pengajuan', [
            'no_whatsapp'                  => '081234567890',
            'semester'                     => 6,
            'nama_lomba'                   => 'Lomba Duplicate MK Array',
            'bidang_id'                    => $bidang->id,
            'tingkatan_id'                 => 1,
            'tahapan_id'                   => 2,
            'mata_kuliah_ids'              => [$mk->id, $mk->id], // Duplikat
            'link_sertifikat'              => 'https://example.com/sertifikat.pdf',
            'link_surat_tugas_mahasiswa'   => 'https://example.com/st-mhs.pdf',
            'link_surat_tugas_dosen'       => 'https://example.com/st-dsn.pdf',
            'link_poster'                  => 'https://example.com/poster.jpg',
            'link_sosmed'                  => 'https://example.com/sosmed',
            'status_surat_tugas_mahasiswa' => 1,
            'status_surat_tugas_dosen'     => 1,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['mata_kuliah_ids']);
    }

    // ═══════════════════════════════════════════════════════════════
    // T12.2 — RESPONSE CONSISTENCY
    // ═══════════════════════════════════════════════════════════════

    /**
     * 9. Konsistensi Response: Format Success Response {success: true, message: ..., data: ...}.
     */
    public function test_response_format_consistency_on_success(): void
    {
        Sanctum::actingAs($this->mhsSI);

        // 9a. Standard single object
        $resMe = $this->getJson('/api/auth/me');
        $resMe->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'nim_nip',
                    'nama',
                    'email',
                    'roles',
                ],
            ])
            ->assertJsonPath('success', true);

        // 9b. Standard paginated list
        $resList = $this->getJson('/api/mahasiswa/pengajuan');
        $resList->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'items',
                    'meta' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                    ],
                ],
            ])
            ->assertJsonPath('success', true);

        // 9c. Standard created response (201)
        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mk = $this->getValidMkForMahasiswa($this->mhsSI, 'Programming');
        $resCreate = $this->postJson('/api/mahasiswa/pengajuan', [
            'no_whatsapp'                  => '081234567890',
            'semester'                     => 6,
            'nama_lomba'                   => 'Format Consistency Test Lomba',
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
        ]);
        $resCreate->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data'])
            ->assertJsonPath('success', true);
    }

    /**
     * 10. Konsistensi Response: 404 ketika resource tidak ditemukan.
     */
    public function test_404_when_resource_not_found(): void
    {
        Sanctum::actingAs($this->mhsSI);
        $this->getJson('/api/mahasiswa/pengajuan/999999')->assertStatus(404);

        Sanctum::actingAs($this->verifSI);
        $this->getJson('/api/verifikator/pengajuan/999999')->assertStatus(404);

        Sanctum::actingAs($this->tendik);
        $this->getJson('/api/tendik/pengajuan/999999')->assertStatus(404);

        Sanctum::actingAs($this->wadek);
        $this->putJson('/api/wadek/matriks/999999', ['min_sks' => 2])->assertStatus(404);
        $this->deleteJson('/api/wadek/verifikator/999999')->assertStatus(404);
        $this->deleteJson('/api/wadek/bidang-mata-kuliah/999999')->assertStatus(404);
    }

    // ═══════════════════════════════════════════════════════════════
    // T12.3 — DATA INTEGRITY & IMMUTABILITY
    // ═══════════════════════════════════════════════════════════════

    /**
     * 11. Immutability Snapshot: Perubahan MatriksKonversi oleh Wadek tidak mempengaruhi pengajuan lama.
     */
    public function test_snapshot_matriks_is_immutable_after_wadek_updates(): void
    {
        // 1. Mahasiswa submit pengajuan (Tingkatan=1, Tahapan=2 -> min=2, max=3, huruf=AB)
        $pengajuan = $this->submitValidPengajuan($this->mhsSI, 'Lomba Immutability Test');

        $this->assertEquals(2, $pengajuan->snapshot_min_sks);
        $this->assertEquals(3, $pengajuan->snapshot_max_sks);
        $this->assertEquals('AB', $pengajuan->snapshot_huruf_nilai);

        // 2. Wadek mengubah baris matriks tersebut
        Sanctum::actingAs($this->wadek);
        $matriks = MatriksKonversi::where('tingkatan_id', 1)->where('tahapan_id', 2)->first();
        $this->putJson("/api/wadek/matriks/{$matriks->id}", [
            'min_sks'     => 4,
            'max_sks'     => 6,
            'huruf_nilai' => 'A',
        ])->assertStatus(200);

        // 3. Verifikasi pengajuan lama tetap memiliki snapshot awal
        $pengajuan->refresh();
        $this->assertEquals(2, $pengajuan->snapshot_min_sks);
        $this->assertEquals(3, $pengajuan->snapshot_max_sks);
        $this->assertEquals('AB', $pengajuan->snapshot_huruf_nilai);
    }

    /**
     * 12. Data Integrity: End-to-End Lifecycle & Timestamp Accuracy.
     */
    public function test_full_lifecycle_data_integrity_and_timestamps(): void
    {
        // 1. Submit
        $pengajuan = $this->submitValidPengajuan($this->mhsSI, 'Lomba Lifecycle Complete');
        $this->assertEquals('pending', $pengajuan->status);
        $this->assertNull($pengajuan->verifikator_id);
        $this->assertNull($pengajuan->verified_at);
        $this->assertNull($pengajuan->tendik_id);
        $this->assertNull($pengajuan->processed_at);

        // Pivot pengajuan_mata_kuliah harus ada, sks_snapshot terisi, huruf_nilai NULL
        $pivot = PengajuanMataKuliah::where('pengajuan_id', $pengajuan->id)->first();
        $this->assertNotNull($pivot);
        $this->assertGreaterThan(0, $pivot->sks_snapshot);
        $this->assertNull($pivot->huruf_nilai);

        // 2. Terima oleh Verifikator SI
        Sanctum::actingAs($this->verifSI);
        $resTerima = $this->postJson("/api/verifikator/pengajuan/{$pengajuan->id}/terima");
        $resTerima->assertStatus(200);

        $pengajuan->refresh();
        $this->assertEquals('diterima', $pengajuan->status);
        $this->assertEquals($this->verifSI->id, $pengajuan->verifikator_id);
        $this->assertNotNull($pengajuan->verified_at);
        $this->assertNull($pengajuan->tendik_id);
        $this->assertNull($pengajuan->processed_at);

        // 3. Finalisasi oleh Tendik
        Sanctum::actingAs($this->tendik);
        $resFinalisasi = $this->postJson("/api/tendik/pengajuan/{$pengajuan->id}/finalisasi", [
            'nilai_per_mk' => [
                [
                    'mk_id'       => $pivot->mata_kuliah_id,
                    'huruf_nilai' => $pengajuan->snapshot_huruf_nilai, // 'AB'
                ],
            ],
            'link_sk_konversi' => 'https://siakad.example.com/sk/123.pdf',
        ]);
        $resFinalisasi->assertStatus(200);

        $pengajuan->refresh();
        $this->assertEquals('selesai', $pengajuan->status);
        $this->assertEquals($this->tendik->id, $pengajuan->tendik_id);
        $this->assertNotNull($pengajuan->processed_at);
        $this->assertEquals('https://siakad.example.com/sk/123.pdf', $pengajuan->link_sk_konversi);

        // Pivot huruf_nilai sekarang terisi dan sama dengan snapshot
        $pivot->refresh();
        $this->assertEquals($pengajuan->snapshot_huruf_nilai, $pivot->huruf_nilai);
    }
}
