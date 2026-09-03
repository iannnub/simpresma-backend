<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     * Login dummy (Tahap 1) — email + password
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::with(['prodi', 'roles'])->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $token = $user->createToken('simpresma-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data'    => [
                'token' => $token,
                'user'  => $this->formatUser($user),
            ],
        ]);
    }

    /**
     * POST /api/auth/logout
     * Middleware: auth:sanctum
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * GET /api/auth/me
     * Middleware: auth:sanctum
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['prodi', 'roles']);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => $this->formatUser($user),
        ]);
    }

    /**
     * POST /api/auth/connect-telegram
     * Hubungkan akun user dengan Telegram Chat ID.
     * Middleware: auth:sanctum
     */
    public function connectTelegram(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'telegram_chat_id' => ['required', 'string', 'regex:/^\d+$/'],
        ], [
            'telegram_chat_id.required' => 'Chat ID Telegram wajib diisi.',
            'telegram_chat_id.regex'    => 'Chat ID Telegram harus berupa angka.',
        ]);

        $user = $request->user();

        // Cek apakah chat_id sudah dipakai akun lain
        $existing = User::where('telegram_chat_id', $validated['telegram_chat_id'])
            ->where('id', '!=', $user->id)
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'telegram_chat_id' => ['Chat ID ini sudah terhubung dengan akun lain.'],
            ]);
        }

        $user->update([
            'telegram_chat_id'      => $validated['telegram_chat_id'],
            'telegram_connected_at' => now(),
        ]);

        // Kirim pesan konfirmasi ke user
        try {
            app(\App\Services\TelegramService::class)->sendMessage(
                $validated['telegram_chat_id'],
                implode("\n", [
                    "✅ <b>Akun Berhasil Terhubung!</b>",
                    "",
                    "Halo <b>{$user->nama}</b>,",
                    "Akun SIMPRESMA Anda kini terhubung dengan Telegram.",
                    "",
                    "Anda akan menerima notifikasi real-time untuk setiap update pengajuan prestasi.",
                    "",
                    "— SIMPRESMA @" . config('services.telegram.bot_username'),
                ])
            );
        } catch (\Throwable) {
            // Jangan gagalkan request jika Telegram API error
        }

        return response()->json([
            'success' => true,
            'message' => 'Akun Telegram berhasil dihubungkan.',
            'data'    => $this->formatUser($user->fresh(['prodi', 'roles'])),
        ]);
    }

    /**
     * POST /api/auth/disconnect-telegram
     * Putuskan koneksi Telegram dari akun user.
     * Middleware: auth:sanctum
     */
    public function disconnectTelegram(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'telegram_chat_id'      => null,
            'telegram_connected_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Koneksi Telegram berhasil diputus.',
            'data'    => $this->formatUser($user->fresh(['prodi', 'roles'])),
        ]);
    }

    // ─── Private Helper ──────────────────────────────────

    private function formatUser(User $user): array
    {
        return [
            'id'                     => $user->id,
            'nim_nip'                => $user->nim_nip,
            'nama'                   => $user->nama,
            'email'                  => $user->email,
            'no_whatsapp'            => $user->no_whatsapp,
            'telegram_connected'     => !empty($user->telegram_chat_id),
            'telegram_connected_at'  => $user->telegram_connected_at?->toISOString(),
            'prodi'                  => $user->prodi ? [
                'id'        => $user->prodi->id,
                'singkatan' => $user->prodi->singkatan,
                'nama'      => $user->prodi->nama,
            ] : null,
            'roles' => is_array($user->role) && !empty($user->role) ? $user->role : $user->roles->pluck('name')->toArray(),
        ];
    }
}
