<?php

namespace App\Listeners;

use App\Events\PengajuanSubmitted;
use App\Models\VerifikatorProdi;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Listener terpisah: notifikasi ke verifikator saat mahasiswa submit pengajuan baru.
 */
class SendNewPengajuanNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 2;

    public function __construct(
        protected TelegramService $telegram
    ) {}

    public function handle(PengajuanSubmitted $event): void
    {
        $pengajuan = $event->pengajuan->load(['mahasiswa', 'prodi', 'bidang', 'tahapan']);

        $namaMhs   = $pengajuan->mahasiswa?->nama ?? '-';
        $namaLomba = $pengajuan->nama_lomba ?? '-';
        $prodi     = $pengajuan->prodi?->singkatan ?? '-';
        $bidang    = $pengajuan->bidang?->nama ?? '-';
        $tahapan   = $pengajuan->tahapan?->nama ?? '-';
        $botName   = config('services.telegram.bot_username', 'simpresma_unej_bot');

        // Ambil semua verifikator aktif untuk prodi pengajuan ini
        $verifikatorIds = VerifikatorProdi::where('prodi_id', $pengajuan->prodi_id)
            ->where('is_active', 1)
            ->pluck('user_id');

        $verifikators = User::whereIn('id', $verifikatorIds)
            ->whereNotNull('telegram_chat_id')
            ->get();

        if ($verifikators->isEmpty()) {
            return;
        }

        $text = implode("\n", [
            "📨 <b>Pengajuan Prestasi Baru!</b>",
            "",
            "Ada pengajuan baru yang menunggu verifikasi Anda.",
            "",
            "• Mahasiswa : {$namaMhs}",
            "• Prodi     : {$prodi}",
            "• Lomba     : {$namaLomba}",
            "• Bidang    : {$bidang}",
            "• Capaian   : {$tahapan}",
            "• ID        : #{$pengajuan->id}",
            "",
            "Silakan login ke SIMPRESMA untuk memverifikasi.",
            "— @{$botName}",
        ]);

        foreach ($verifikators as $verifikator) {
            $this->telegram->sendMessage($verifikator->telegram_chat_id, $text);
        }
    }
}
