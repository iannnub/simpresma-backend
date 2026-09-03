<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatriksKonversi extends Model
{
    protected $table = 'matriks_konversi';

    protected $fillable = [
        'tingkatan_id',
        'tahapan_id',
        'min_sks',
        'max_sks',
        'huruf_nilai',
        'is_active',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'min_sks' => 'integer',
            'max_sks' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function tingkatan(): BelongsTo
    {
        return $this->belongsTo(TingkatanLomba::class, 'tingkatan_id');
    }

    public function tahapan(): BelongsTo
    {
        return $this->belongsTo(TahapanLomba::class, 'tahapan_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scope: kombinasi matriks yang menghasilkan konversi (min_sks tidak NULL)
    public function scopeValid(Builder $query): Builder
    {
        return $query->whereNotNull('min_sks')->where('is_active', true);
    }
}
