<?php

namespace App\Listeners;

use App\Events\PengajuanStatusChanged;
use App\Models\User;
use App\Models\VerifikatorProdi;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendTelegramNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Jumlah retry jika job gagal
     */
    public int $tries = 2;

    public function __construct(
        protected TelegramService $telegram
    ) {}

    public function handle(PengajuanStatusChanged $event): void
    {
        $pengajuan = $event->pengajuan->load([
            'mahasiswa',
            'verifikator',
            'tendik',
            'bidang',
            'tingkatan',
            'tahapan',
            'prodi',
        ]);

        $newStatus  = $event->newStatus;
        $namaMhs    = $pengajuan->mahasiswa?->nama ?? 'Mahasiswa';
        $namaLomba  = $pengajuan->nama_lomba ?? '-';
        $bidang     = $pengajuan->bidang?->nama ?? '-';
        $prodi      = $pengajuan->prodi?->singkatan ?? '-';

        // ─── 1. Notifikasi ke Mahasiswa ───────────────────────────────────
        $mhsChatId = $pengajuan->mahasiswa?->telegram_chat_id;

        if ($mhsChatId && in_array($newStatus, ['pending', 'diterima', 'ditolak', 'selesai'])) {
            $mhsText = $this->buildMahasiswaMessage($newStatus, $namaLomba, $pengajuan);
            $this->telegram->sendMessage($mhsChatId, $mhsText);
        }

        // ─── 2. Notifikasi ke Verifikator (saat pengajuan baru masuk berstatus pending) ─
        if ($newStatus === 'pending') {
            $this->notifyVerifikator($namaLomba, $namaMhs, $prodi, (int) $pengajuan->prodi_id, $pengajuan->id);
        }

        // ─── 3. Notifikasi ke Tendik (ketika pengajuan diterima oleh verifikator) ──
        if ($newStatus === 'diterima') {
            $this->notifyTendik($namaLomba, $namaMhs, $prodi, $pengajuan->id);
        }
    }

    // ─── Builder Messages ────────────────────────────────────────────────

    protected function buildMahasiswaMessage(string $status, string $namaLomba, $pengajuan): string
    {
        $botName = config('services.telegram.bot_username', 'simpresma_unej_bot');

        return match ($status) {
            'pending' => implode("\n", [
                "📤 <b>Pengajuan Prestasi Terkirim!</b>",
                "",
                "Halo <b>{$pengajuan->mahasiswa?->nama}</b>,",
                "Pengajuan prestasi Anda telah berhasil dikirim ke sistem SIMPRESMA.",
                "",
                "📋 <b>Detail Pengajuan:</b>",
                "• Lomba    : {$namaLomba}",
                "• Bidang   : {$pengajuan->bidang?->nama}",
                "• Status   : ⏳ Menunggu Verifikasi",
                "",
                "Pengajuan Anda saat ini berada dalam antrean Tim Verifikator Program Studi.",
                "",
                "— SIMPRESMA @{$botName}",
            ]),

            'diterima' => implode("\n", [
                "✅ <b>Pengajuan Diterima!</b>",
                "",
                "Halo <b>{$pengajuan->mahasiswa?->nama}</b>,",
                "Pengajuan prestasi Anda telah <b>disetujui</b> oleh Dosen Verifikator.",
                "",
                "📋 <b>Detail Pengajuan:</b>",
                "• Lomba    : {$namaLomba}",
                "• Bidang   : {$pengajuan->bidang?->nama}",
                "• Status   : ✅ Diterima",
                ($pengajuan->feedback_verifikator ? "• Catatan  : <i>\"{$pengajuan->feedback_verifikator}\"</i>" : ""),
                "",
                "Pengajuan kini diteruskan ke antrean Tendik untuk finalisasi nilai & SK.",
                "",
                "— SIMPRESMA @{$botName}",
            ]),

            'ditolak' => implode("\n", [
                "❌ <b>Pengajuan Ditolak</b>",
                "",
                "Halo <b>{$pengajuan->mahasiswa?->nama}</b>,",
                "Maaf, pengajuan prestasi Anda <b>tidak disetujui</b>.",
                "",
                "📋 <b>Detail Pengajuan:</b>",
                "• Lomba    : {$namaLomba}",
                "• Alasan   : <i>\"{$pengajuan->feedback_verifikator}\"</i>",
                "",
                "Silakan periksa catatan revisi di atas dan ajukan kembali jika berkas telah diperbaiki.",
                "",
                "— SIMPRESMA @{$botName}",
            ]),

            'selesai' => implode("\n", [
                "🎉 <b>Konversi SKS & Prestasi Selesai!</b>",
                "",
                "Halo <b>{$pengajuan->mahasiswa?->nama}</b>,",
                "Proses finalisasi prestasi Anda telah <b>selesai</b> diproses oleh Tendik.",
                "",
                "📋 <b>Detail:</b>",
                "• Lomba    : {$namaLomba}",
                "• Status   : 🎓 Selesai",
                ($pengajuan->link_sk_konversi ? "• Link SK  : {$pengajuan->link_sk_konversi}" : ""),
                ($pengajuan->catatan_tendik ? "• Catatan  : <i>\"{$pengajuan->catatan_tendik}\"</i>" : ""),
                "",
                "Selamat! Seluruh proses pengakuan prestasi dan SKS telah rampung.",
                "",
                "— SIMPRESMA @{$botName}",
            ]),

            default => "Update status pengajuan: {$namaLomba} → {$status}",
        };
    }

    protected function notifyVerifikator(string $namaLomba, string $namaMhs, string $prodi, int $prodiId, int $pengajuanId): void
    {
        $verifikatorUsers = User::whereHas('verifikatorProdis', fn ($q) => $q->where('prodi_id', $prodiId)->where('is_active', 1))
            ->whereNotNull('telegram_chat_id')
            ->get();

        $botName = config('services.telegram.bot_username', 'simpresma_unej_bot');
        $text = implode("\n", [
            "📥 <b>Pengajuan Prestasi Baru Masuk</b>",
            "",
            "Ada pengajuan prestasi baru yang membutuhkan verifikasi Anda:",
            "",
            "• Mahasiswa : {$namaMhs}",
            "• Prodi     : {$prodi}",
            "• Lomba     : {$namaLomba}",
            "• ID        : #{$pengajuanId}",
            "",
            "Silakan buka antrean verifikasi di SIMPRESMA untuk memeriksa berkas.",
            "",
            "— SIMPRESMA @{$botName}",
        ]);

        foreach ($verifikatorUsers as $u) {
            $this->telegram->sendMessage($u->telegram_chat_id, $text);
        }
    }

    protected function notifyTendik(string $namaLomba, string $namaMhs, string $prodi, int $pengajuanId): void
    {
        // Ambil semua user yang punya role tendik dan punya telegram_chat_id
        $tendikUsers = User::whereHas('roles', fn ($q) => $q->where('name', 'tendik'))
            ->whereNotNull('telegram_chat_id')
            ->get();

        $botName = config('services.telegram.bot_username', 'simpresma_unej_bot');
        $text = implode("\n", [
            "📥 <b>Pengajuan Siap Difinalisasi</b>",
            "",
            "Ada pengajuan yang sudah <b>diterima verifikator</b> dan menunggu finalisasi SK.",
            "",
            "• Mahasiswa  : {$namaMhs}",
            "• Prodi      : {$prodi}",
            "• Lomba      : {$namaLomba}",
            "• ID         : #{$pengajuanId}",
            "",
            "Silakan login ke SIMPRESMA untuk memproses.",
            "— @{$botName}",
        ]);

        foreach ($tendikUsers as $tendik) {
            $this->telegram->sendMessage($tendik->telegram_chat_id, $text);
        }
    }
}
