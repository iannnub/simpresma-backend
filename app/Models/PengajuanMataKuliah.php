<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanMataKuliah extends Model
{
    protected $table = 'pengajuan_mata_kuliah';

    public $timestamps = false;

    protected $fillable = [
        'pengajuan_id',
        'mata_kuliah_id',
        'sks_snapshot',
        'huruf_nilai',
    ];

    protected function casts(): array
    {
        return [
            'sks_snapshot' => 'integer',
        ];
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class);
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }
}
