<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GapuraAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GapuraAuthController extends Controller
{
    public function __construct(
        protected GapuraAuthService $gapuraService
    ) {}

    /**
     * GET /api/auth/gapura/redirect
     * Mengarahkan pengguna ke halaman otorisasi login GAPURA UNEJ.
     */
    public function redirect(Request $request): JsonResponse|RedirectResponse
    {
        try {
            if (!$this->gapuraService->isConfigured()) {
                $msg = 'Integrasi GAPURA UNEJ belum aktif. Client ID dan Client Secret belum dikonfigurasi di file .env. Silakan daftarkan aplikasi di portal https://gapura.unej.ac.id/developer.';
                if ($request->expectsJson() || $request->query('format') === 'json') {
                    return response()->json([
                        'success' => false,
                        'message' => $msg,
                    ], 503);
                }
                $frontendUrl = config('services.gapura.frontend_url', 'http://localhost:5173/auth/callback');
                return redirect()->away($frontendUrl . '?' . http_build_query(['error' => $msg]));
            }

            $url = $this->gapuraService->getAuthorizationUrl();

            // Jika dipanggil via AJAX/Fetch oleh Single Page Application (React)
            if ($request->expectsJson() || $request->query('format') === 'json') {
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'redirect_url' => $url,
                    ],
                ]);
            }

            return redirect()->away($url);
        } catch (\Throwable $e) {
            Log::error('Gapura redirect error: ' . $e->getMessage());

            if ($request->expectsJson() || $request->query('format') === 'json') {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            $frontendUrl = config('services.gapura.frontend_url', 'http://localhost:5173/auth/callback');
            return redirect()->away($frontendUrl . '?' . http_build_query([
                'error' => $e->getMessage(),
            ]));
        }
    }

    /**
     * GET /api/auth/gapura/callback
     * Menerima kode otorisasi dari GAPURA, menukar access token, dan menerbitkan sesi Sanctum.
     */
    public function callback(Request $request): RedirectResponse|JsonResponse
    {
        $frontendUrl = config('services.gapura.frontend_url', 'http://localhost:5173/auth/callback');

        // Jika GAPURA mengembalikan pesan penolakan atau error
        if ($request->has('error')) {
            $errorDescription = $request->query('error_description', $request->query('error'));
            Log::warning('GAPURA returned error: ' . $errorDescription);

            return redirect()->away($frontendUrl . '?' . http_build_query([
                'error' => $errorDescription,
            ]));
        }

        $code  = $request->query('code');
        $state = $request->query('state');

        if (empty($code) || empty($state)) {
            return redirect()->away($frontendUrl . '?' . http_build_query([
                'error' => 'Parameter kode atau state otorisasi GAPURA tidak ditemukan.',
            ]));
        }

        try {
            $result = $this->gapuraService->handleCallback($code, $state);

            $user  = $result['user'];
            $token = $result['token'];

            $primaryRole = is_array($user->role) ? ($user->role[0] ?? 'mahasiswa') : ($user->role ?? 'mahasiswa');

            $params = [
                'token' => $token,
                'role'  => $primaryRole,
                'nama'  => $user->nama,
            ];

            return redirect()->away($frontendUrl . '?' . http_build_query($params));
        } catch (\Throwable $e) {
            Log::error('GAPURA callback processing failure: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->away($frontendUrl . '?' . http_build_query([
                'error' => $e->getMessage(),
            ]));
        }
    }
}
