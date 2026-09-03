<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MataKuliah extends Model
{
    protected $table = 'mata_kuliah';

    protected $fillable = [
        'prodi_id',
        'kode_mk',
        'nama_mk',
        'sks',
        'semester',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sks' => 'integer',
            'semester' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function bidangs(): BelongsToMany
    {
        return $this->belongsToMany(BidangLomba::class, 'bidang_mata_kuliah')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function pengajuans(): BelongsToMany
    {
        return $this->belongsToMany(Pengajuan::class, 'pengajuan_mata_kuliah')
            ->withPivot('sks_snapshot', 'huruf_nilai');
    }
}
