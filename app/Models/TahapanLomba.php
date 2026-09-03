<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahapanLomba extends Model
{
    protected $table = 'tahapan_lomba';

    public $timestamps = false;

    protected $fillable = ['kode', 'nama', 'urutan'];

    public function matriksKonversis(): HasMany
    {
        return $this->hasMany(MatriksKonversi::class, 'tahapan_id');
    }

    public function pengajuans(): HasMany
    {
        return $this->hasMany(Pengajuan::class, 'tahapan_id');
    }
}
