<?php

namespace Tests\Unit;

use App\Models\Prodi;
use App\Models\User;
use App\Services\GapuraAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GapuraAuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected GapuraAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GapuraAuthService();
    }

    public function test_get_authorization_url_throws_exception_when_client_id_missing(): void
    {
        Config::set('services.gapura.client_id', null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('GAPURA_CLIENT_ID belum dikonfigurasi');

        $this->service->getAuthorizationUrl();
    }

    public function test_get_authorization_url_generates_valid_url_and_caches_state(): void
    {
        Config::set('services.gapura.url', 'https://gapura.unej.ac.id');
        Config::set('services.gapura.client_id', 'simpresma-client-123');
        Config::set('services.gapura.redirect_uri', 'http://localhost:8000/api/auth/gapura/callback');

        $url = $this->service->getAuthorizationUrl();

        $this->assertStringContainsString('https://gapura.unej.ac.id/oauth/authorize', $url);
        $this->assertStringContainsString('client_id=simpresma-client-123', $url);
        $this->assertStringContainsString('response_type=code', $url);
        $this->assertStringContainsString('state=', $url);

        // Parse query params to verify state was cached
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        $this->assertNotEmpty($params['state']);
        $this->assertTrue(Cache::has('gapura_oauth_state_' . $params['state']));
    }

    public function test_handle_callback_rejects_invalid_state(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CSRF check failed');

        $this->service->handleCallback('dummy-code-123', 'invalid-state-random');
    }

    public function test_find_or_create_user_provisions_new_student_with_prodi_mapping(): void
    {
        $gapuraUser = [
            'personal_id' => '8a81918b-b5cc-4a0e-9613-596b42c026f9',
            'identifier'  => '220210101999',
            'name'        => 'Budi Mahasiswa Baru',
            'email'       => '220210101999@mail.unej.ac.id',
            'category'    => 'mahasiswa',
            'unit'        => 'Program Studi Sistem Informasi',
        ];

        $user = $this->service->findOrCreateUserFromGapura($gapuraUser);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('8a81918b-b5cc-4a0e-9613-596b42c026f9', $user->personal_id);
        $this->assertEquals('220210101999', $user->nim_nip);
        $this->assertEquals('Budi Mahasiswa Baru', $user->nama);
        $this->assertEquals(['mahasiswa'], $user->role);
        $this->assertEquals(1, $user->prodi_id);

        $this->assertDatabaseHas('users', [
            'personal_id' => '8a81918b-b5cc-4a0e-9613-596b42c026f9',
            'nim_nip'     => '220210101999',
        ]);
    }

    public function test_find_or_create_user_updates_existing_user_without_overwriting_roles(): void
    {
        $existing = User::create([
            'nim_nip'  => '198501012010121001',
            'nama'     => 'Dr. Dosen Lama',
            'email'    => 'dosen.lama@test.com',
            'password' => 'secret123',
            'role'     => ['wadek'], // Peran Wadek sudah di-assign sebelumnya
            'prodi_id' => null,
        ]);

        $gapuraUser = [
            'personal_id' => 'dosen-uuid-permanent',
            'identifier'  => '198501012010121001',
            'name'        => 'Dr. Dosen Lama M.Kom.',
            'email'       => 'dosen.lama@test.com',
            'category'    => 'dosen',
            'unit'        => 'Teknologi Informasi',
        ];

        $user = $this->service->findOrCreateUserFromGapura($gapuraUser);

        $this->assertEquals($existing->id, $user->id);
        $this->assertEquals('dosen-uuid-permanent', $user->personal_id);
        $this->assertEquals('Dr. Dosen Lama M.Kom.', $user->nama);
        // Pastikan role wadek tidak tertimpa menjadi verifikator umum
        $this->assertEquals(['wadek'], $user->role);
    }
}
