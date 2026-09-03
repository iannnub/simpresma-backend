<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BidangLomba extends Model
{
    protected $table = 'bidang_lomba';

    protected $fillable = ['nama', 'keterangan', 'is_active'];

    public function mataKuliahs(): BelongsToMany
    {
        return $this->belongsToMany(MataKuliah::class, 'bidang_mata_kuliah')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function pengajuans(): HasMany
    {
        return $this->hasMany(Pengajuan::class, 'bidang_id');
    }
}
