<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TingkatanLomba extends Model
{
    protected $table = 'tingkatan_lomba';

    public $timestamps = false;

    protected $fillable = ['nama', 'urutan'];

    public function matriksKonversis(): HasMany
    {
        return $this->hasMany(MatriksKonversi::class, 'tingkatan_id');
    }

    public function pengajuans(): HasMany
    {
        return $this->hasMany(Pengajuan::class, 'tingkatan_id');
    }
}
