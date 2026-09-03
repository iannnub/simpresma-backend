<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengajuan extends Model
{
    protected $table = 'pengajuan';

    protected $fillable = [
        'user_id',
        'prodi_id',
        'nama_tim',
        'no_whatsapp',
        'nama_lomba',
        'bidang_id',
        'tingkatan_id',
        'tahapan_id',
        'semester',
        'detail_juara',
        'snapshot_min_sks',
        'snapshot_max_sks',
        'snapshot_huruf_nilai',
        'link_sertifikat',
        'status_surat_tugas_mahasiswa',
        'link_surat_tugas_mahasiswa',
        'status_surat_tugas_dosen',
        'link_surat_tugas_dosen',
        'link_poster',
        'link_sosmed',
        'keterangan',
        'status',
        'feedback_verifikator',
        'verifikator_id',
        'verified_at',
        'link_sk_konversi',
        'catatan_tendik',
        'tendik_id',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status_surat_tugas_mahasiswa' => 'boolean',
            'status_surat_tugas_dosen' => 'boolean',
            'verified_at' => 'datetime',
            'processed_at' => 'datetime',
            'snapshot_min_sks' => 'integer',
            'snapshot_max_sks' => 'integer',
        ];
    }

    // ─── Relasi ──────────────────────────────────────────

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(BidangLomba::class, 'bidang_id');
    }

    public function tingkatan(): BelongsTo
    {
        return $this->belongsTo(TingkatanLomba::class, 'tingkatan_id');
    }

    public function tahapan(): BelongsTo
    {
        return $this->belongsTo(TahapanLomba::class, 'tahapan_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_id');
    }

    public function tendik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tendik_id');
    }

    public function mataKuliahs(): BelongsToMany
    {
        return $this->belongsToMany(MataKuliah::class, 'pengajuan_mata_kuliah')
            ->withPivot('sks_snapshot', 'huruf_nilai');
    }

    public function pengajuanMataKuliahs(): HasMany
    {
        return $this->hasMany(PengajuanMataKuliah::class);
    }
}
