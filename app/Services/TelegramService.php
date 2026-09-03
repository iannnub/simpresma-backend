<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $token;
    protected string $baseUrl;

    public function __construct()
    {
        $this->token   = config('services.telegram.bot_token', '');
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}";
    }

    /**
     * Kirim pesan teks ke Telegram Chat ID.
     * Tidak melempar exception agar proses utama tidak terganggu.
     */
    public function sendMessage(string $chatId, string $text): bool
    {
        if (empty($this->token) || empty($chatId)) {
            return false;
        }

        try {
            $response = Http::timeout(5)->post("{$this->baseUrl}/sendMessage", [
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'HTML',
            ]);

            if (!$response->successful()) {
                Log::warning('[TelegramService] API error', [
                    'chat_id' => $chatId,
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('[TelegramService] Exception', [
                'chat_id' => $chatId,
                'error'   => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Set webhook URL di Telegram (opsional, untuk mode webhook).
     */
    public function setWebhook(string $url): bool
    {
        try {
            $response = Http::post("{$this->baseUrl}/setWebhook", ['url' => $url]);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('[TelegramService] setWebhook exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete webhook (kembali ke mode polling/manual).
     */
    public function deleteWebhook(): bool
    {
        try {
            $response = Http::post("{$this->baseUrl}/deleteWebhook");
            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
