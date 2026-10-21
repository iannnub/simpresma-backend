<?php

namespace App\Services;

use App\Models\Prodi;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GapuraAuthService
{
    /**
     * Memeriksa apakah konfigurasi kredensial GAPURA UNEJ telah terpasang.
     */
    public function isConfigured(): bool
    {
        return !empty(config('services.gapura.client_id')) && !empty(config('services.gapura.client_secret'));
    }

    /**
     * Membangun URL Otorisasi resmi GAPURA UNEJ dengan proteksi anti-CSRF (state).
     */
    public function getAuthorizationUrl(): string
    {
        $clientId = config('services.gapura.client_id');
        $baseUrl  = config('services.gapura.url', 'https://gapura.unej.ac.id');
        $redirect = config('services.gapura.redirect_uri');

        if (empty($clientId)) {
            throw new \RuntimeException('GAPURA_CLIENT_ID belum dikonfigurasi. Daftarkan layanan di https://gapura.unej.ac.id/developer.');
        }

        // Generate state acak untuk verifikasi CSRF (berlaku 10 menit)
        $state = Str::random(40);
        Cache::put('gapura_oauth_state_' . $state, true, now()->addMinutes(10));

        $params = [
            'client_id'     => $clientId,
            'redirect_uri'  => $redirect,
            'response_type' => 'code',
            'scope'         => 'openid profile email',
            'state'         => $state,
        ];

        return rtrim($baseUrl, '/') . '/oauth/authorize?' . http_build_query($params);
    }

    /**
     * Menangani callback dari GAPURA UNEJ: menukar code dengan token, mengambil profil,
     * serta mencocokkan atau membuat pengguna baru (auto-provisioning).
     *
     * @return array{user: User, token: string}
     */
    public function handleCallback(string $code, string $state): array
    {
        // 1. Verifikasi state anti-CSRF
        $cached = Cache::pull('gapura_oauth_state_' . $state);
        if (!$cached) {
            throw new \InvalidArgumentException('State sesi login GAPURA tidak valid atau telah kadaluarsa (CSRF check failed).');
        }

        $baseUrl      = config('services.gapura.url', 'https://gapura.unej.ac.id');
        $clientId     = config('services.gapura.client_id');
        $clientSecret = config('services.gapura.client_secret');
        $redirectUri  = config('services.gapura.redirect_uri');

        // 2. Menukar authorization code dengan Access Token
        $tokenUrl = rtrim($baseUrl, '/') . '/oauth/token';
        $tokenResponse = Http::asForm()
            ->timeout(15)
            ->post($tokenUrl, [
                'grant_type'    => 'authorization_code',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri'  => $redirectUri,
                'code'          => $code,
            ]);

        if (!$tokenResponse->successful()) {
            $err = $tokenResponse->json('error_description') ?? $tokenResponse->json('message') ?? 'Gagal menukar kode otorisasi GAPURA.';
            throw new \RuntimeException($err);
        }

        $accessToken = $tokenResponse->json('access_token');
        if (empty($accessToken)) {
            throw new \RuntimeException('Access token tidak ditemukan dalam respon GAPURA.');
        }

        // 3. Mengambil data profil civitas dari GAPURA (/api/user)
        $userUrl = rtrim($baseUrl, '/') . '/api/user';
        $userResponse = Http::withToken($accessToken)
            ->timeout(15)
            ->get($userUrl);

        if (!$userResponse->successful()) {
            throw new \RuntimeException('Gagal mengambil data profil pengguna dari server GAPURA UNEJ.');
        }

        $gapuraUser = $userResponse->json();

        // 4. Sinkronisasi atau Auto-Register Pengguna ke Database SIMPRESMA
        $user = $this->findOrCreateUserFromGapura($gapuraUser);

        // 5. Terbitkan Token Sesi Sanctum
        $token = $user->createToken('gapura_session_' . Str::random(8))->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    /**
     * Mencocokkan data GAPURA dengan tabel users di SIMPRESMA.
     * Mengutamakan kecocokan personal_id, lalu nim_nip, lalu email.
     */
    public function findOrCreateUserFromGapura(array $gapuraUser): User
    {
        $personalId = $gapuraUser['personal_id'] ?? $gapuraUser['sub'] ?? null;
        $identifier = $gapuraUser['identifier'] ?? $gapuraUser['username'] ?? null;
        $name       = $gapuraUser['name'] ?? 'Civitas UNEJ';
        $email      = $gapuraUser['email'] ?? ($identifier ? "{$identifier}@mail.unej.ac.id" : null);
        $category   = strtolower($gapuraUser['category'] ?? 'mahasiswa');
        $unitName   = $gapuraUser['unit'] ?? '';

        // Deteksi Prodi berdasarkan unit kerja
        $prodiId = $this->resolveProdiId($unitName);

        // 1. Cari pengguna berdasarkan personal_id
        $user = null;
        if (!empty($personalId)) {
            $user = User::where('personal_id', $personalId)->first();
        }

        // 2. Fallback pencocokan berdasarkan NIM / NIP
        if (!$user && !empty($identifier)) {
            $user = User::where('nim_nip', $identifier)->first();
        }

        // 3. Fallback pencocokan berdasarkan email
        if (!$user && !empty($email)) {
            $user = User::where('email', $email)->first();
        }

        if ($user) {
            // Update data profil terbaru dari GAPURA
            if (!empty($personalId) && empty($user->personal_id)) {
                $user->personal_id = $personalId;
            }
            if (!empty($name) && $user->nama !== $name) {
                $user->nama = $name;
            }
            if ($prodiId && empty($user->prodi_id)) {
                $user->prodi_id = $prodiId;
            }
            $user->save();

            return $user;
        }

        // 4. Jika pengguna belum terdaftar, lakukan Auto-Provisioning (Registrasi Otomatis)
        $initialRole = match ($category) {
            'dosen' => ['verifikator'],
            'tendik' => ['tendik'],
            default => ['mahasiswa'],
        };

        return User::create([
            'personal_id' => $personalId,
            'nim_nip'     => $identifier,
            'nama'        => $name,
            'email'       => $email,
            'password'    => Hash::make(Str::random(32)), // Password random karena auth via Gapura
            'role'        => $initialRole,
            'prodi_id'    => $prodiId,
        ]);
    }

    /**
     * Memetakan nama unit kerja dari GAPURA ke program studi di SIMPRESMA.
     */
    protected function resolveProdiId(string $unitName): ?int
    {
        if (empty($unitName)) {
            return null;
        }

        $lower = strtolower($unitName);

        if (str_contains($lower, 'sistem informasi')) {
            return 1;
        }
        if (str_contains($lower, 'teknologi informasi')) {
            return 2;
        }
        if (str_contains($lower, 'informatika')) {
            return 3;
        }

        // Pencarian parsial ke model Prodi jika ada prodi baru
        return Prodi::where('nama', 'like', "%{$unitName}%")->value('id');
    }
}
