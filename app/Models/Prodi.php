<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prodi extends Model
{
    protected $table = 'prodi';

    protected $fillable = ['kode', 'singkatan', 'nama'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function mataKuliahs(): HasMany
    {
        return $this->hasMany(MataKuliah::class);
    }

    public function verifikatorProdis(): HasMany
    {
        return $this->hasMany(VerifikatorProdi::class);
    }
}
