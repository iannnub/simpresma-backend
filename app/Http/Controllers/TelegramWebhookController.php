<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(
        protected TelegramService $telegram
    ) {}

    /**
     * POST /api/telegram/webhook
     * Menerima update dari Telegram Bot API.
     * Tangani command /start → balas dengan Chat ID user.
     */
    public function handle(Request $request): JsonResponse
    {
        $update = $request->all();

        Log::info('[TelegramWebhook] Incoming update', ['update' => $update]);

        $message  = $update['message'] ?? $update['channel_post'] ?? null;

        if (!$message) {
            return response()->json(['ok' => true]);
        }

        $chatId = $message['chat']['id'] ?? null;
        $text   = trim($message['text'] ?? '');

        if (!$chatId) {
            return response()->json(['ok' => true]);
        }

        // Handle /start command
        if (str_starts_with($text, '/start')) {
            $firstName = $message['from']['first_name'] ?? 'pengguna';
            $replyText = implode("\n", [
                "👋 Halo, <b>{$firstName}</b>!",
                "",
                "Selamat datang di <b>SIMPRESMA Bot</b> (@" . config('services.telegram.bot_username') . ").",
                "",
                "🔑 <b>Chat ID kamu adalah:</b>",
                "<code>{$chatId}</code>",
                "",
                "Salin angka di atas dan tempelkan di halaman <b>Profil → Hubungkan Telegram</b> pada aplikasi SIMPRESMA.",
                "",
                "Setelah terhubung, kamu akan mendapat notifikasi real-time untuk setiap update pengajuan prestasimu.",
            ]);

            $this->telegram->sendMessage((string) $chatId, $replyText);
        }

        return response()->json(['ok' => true]);
    }
}
