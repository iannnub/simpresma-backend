<?php

namespace Tests\Feature;

use App\Models\BidangLomba;
use App\Models\BidangMataKuliah;
use App\Models\MataKuliah;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected User $mhsSI;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mhsSI = User::where('email', 'mhs.si@test.com')->first();
    }

    private function getValidMkForMahasiswa(User $mahasiswa, string $namaBidang): MataKuliah
    {
        $bidang = BidangLomba::where('nama', $namaBidang)->first();
        return BidangMataKuliah::where('bidang_id', $bidang->id)
            ->where('is_active', 1)
            ->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $mahasiswa->prodi_id)->where('is_active', 1))
            ->first()
            ->mataKuliah;
    }

    /**
     * 1. Response harus memiliki HTTP Security Headers standar industri.
     */
    public function test_api_responses_include_security_headers(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'mhs.si@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * 2. SSRF Protection: URL yang mengarah ke localhost atau IP privat ditolak.
     */
    public function test_ssrf_urls_are_rejected(): void
    {
        Sanctum::actingAs($this->mhsSI);

        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mk = $this->getValidMkForMahasiswa($this->mhsSI, 'Programming');

        $basePayload = [
            'no_whatsapp'                  => '081234567890',
            'semester'                     => 6,
            'nama_lomba'                   => 'Lomba Anti SSRF',
            'bidang_id'                    => $bidang->id,
            'tingkatan_id'                 => 1,
            'tahapan_id'                   => 2,
            'mata_kuliah_ids'              => [$mk->id],
            'link_sertifikat'              => 'http://127.0.0.1/malicious.pdf', // Localhost IP
            'link_surat_tugas_mahasiswa'   => 'http://localhost/doc.pdf',     // Localhost hostname
            'link_surat_tugas_dosen'       => 'https://example.com/st-dsn.pdf',
            'link_poster'                  => 'https://example.com/poster.jpg',
            'link_sosmed'                  => 'https://example.com/sosmed',
            'status_surat_tugas_mahasiswa' => 1,
            'status_surat_tugas_dosen'     => 1,
        ];

        $response = $this->postJson('/api/mahasiswa/pengajuan', $basePayload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['link_sertifikat', 'link_surat_tugas_mahasiswa']);
    }

    /**
     * 3. XSS Sanitization: Script HTML dalam nama_lomba disanitasi sebelum disimpan.
     */
    public function test_xss_inputs_are_sanitized(): void
    {
        Sanctum::actingAs($this->mhsSI);

        $bidang = BidangLomba::where('nama', 'Programming')->first();
        $mk = $this->getValidMkForMahasiswa($this->mhsSI, 'Programming');

        $payload = [
            'no_whatsapp'                  => '081234567890',
            'semester'                     => 6,
            'nama_lomba'                   => '<script>alert("xss")</script>Lomba Clean Name',
            'nama_tim'                     => '<b>Tim Hore</b>',
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

        $res = $this->postJson('/api/mahasiswa/pengajuan', $payload);
        $res->assertStatus(201);

        $pengajuan = Pengajuan::find($res->json('data.id'));
        $this->assertEquals('Lomba Clean Name', $pengajuan->nama_lomba);
        $this->assertEquals('Tim Hore', $pengajuan->nama_tim);
        $this->assertStringNotContainsString('<script>', $pengajuan->nama_lomba);
    }

    /**
     * 4. Health Check Endpoint: Mengembalikan status 200 dan status layanan.
     */
    public function test_health_check_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'services' => [
                'database',
                'cache',
                'telegram',
            ],
        ]);
        $this->assertEquals('healthy', $response->json('status'));
        $this->assertEquals('connected', $response->json('services.database'));
    }
}
