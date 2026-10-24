<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RekeningAnggaran extends Model
{
    use HasFactory;

    protected $table = 'rekening_anggaran';

    protected $fillable = ['kode', 'nama', 'jenis', 'parent_id', 'level', 'urutan', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
