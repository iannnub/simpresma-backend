<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BidangMataKuliah extends Model
{
    protected $table = 'bidang_mata_kuliah';

    protected $fillable = ['bidang_id', 'mata_kuliah_id', 'is_active'];

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(BidangLomba::class, 'bidang_id');
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }
}
