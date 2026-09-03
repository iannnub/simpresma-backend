<?php

namespace App\Events;

use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PengajuanStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param Pengajuan $pengajuan  Pengajuan yang baru diubah statusnya
     * @param string    $newStatus  Status baru: 'diterima' | 'ditolak' | 'selesai'
     * @param User      $changedBy  User yang melakukan perubahan (verifikator atau tendik)
     */
    public function __construct(
        public readonly Pengajuan $pengajuan,
        public readonly string $newStatus,
        public readonly User $changedBy,
    ) {}
}
