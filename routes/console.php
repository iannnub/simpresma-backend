<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('telegram:poll', function (\App\Services\TelegramService $telegramService) {
    $token = config('services.telegram.bot_token');
    if (empty($token)) {
        $this->error('TELEGRAM_BOT_TOKEN belum disetel di .env!');
        return;
    }

    $botName = config('services.telegram.bot_username', 'simpresma_unej_bot');
    $this->info("Memulai Telegram Bot Poller (@{$botName})...");
    $this->info("Kirim /start ke @{$botName} di Telegram untuk mendapatkan Chat ID Anda.\n");
    $this->info("Tekan Ctrl+C untuk berhenti.\n");

    $offset = 0;

    while (true) {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(25)->get("https://api.telegram.org/bot{$token}/getUpdates", [
                'offset'  => $offset,
                'timeout' => 20,
            ]);

            if ($response->successful()) {
                $updates = $response->json('result') ?? [];

                foreach ($updates as $update) {
                    $offset = $update['update_id'] + 1;
                    $message = $update['message'] ?? null;

                    if (!$message) {
                        continue;
                    }

                    $chatId    = (string) ($message['chat']['id'] ?? '');
                    $firstName = $message['from']['first_name'] ?? 'Pengguna';
                    $username  = $message['from']['username'] ?? '-';
                    $text      = trim($message['text'] ?? '');

                    $this->line("[Incoming Telegram] Dari: {$firstName} (@{$username}) | Chat ID: {$chatId} | Pesan: \"{$text}\"");

                    if (str_starts_with($text, '/start')) {
                        $replyText = implode("\n", [
                            "👋 Halo, <b>{$firstName}</b>!",
                            "",
                            "Selamat datang di <b>SIMPRESMA Bot</b> (@{$botName}).",
                            "",
                            "🔑 <b>Chat ID kamu adalah:</b>",
                            "<code>{$chatId}</code>",
                            "",
                            "📋 <b>Langkah berikutnya:</b>",
                            "1. Salin angka Chat ID di atas.",
                            "2. Buka SIMPRESMA di menu <b>Profil</b> (klik avatar di pojok kiri bawah).",
                            "3. Tempelkan Chat ID dan klik <b>Hubungkan Akun</b>.",
                            "",
                            "Setelah terhubung, kamu akan otomatis menerima notifikasi real-time saat status pengajuan prestasimu diverifikasi!",
                        ]);

                        $telegramService->sendMessage($chatId, $replyText);
                        $this->info(" -> Membalas Chat ID {$chatId} ke {$firstName}. Berhasil terkirim!\n");
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->warn("Koneksi polling terputus: " . $e->getMessage());
            sleep(3);
        }

        usleep(500000);
    }
})->purpose('Jalankan polling Telegram bot untuk menerima pesan /start dan membalas Chat ID pengguna');
