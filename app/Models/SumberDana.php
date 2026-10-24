<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SumberDana extends Model
{
    use HasFactory;

    protected $table = 'sumber_dana';

    protected $fillable = ['kode', 'nama', 'keterangan', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function pemasukan(): HasMany
    {
        return $this->hasMany(Pemasukan::class);
    }

    public function pengeluaran(): HasMany
    {
        return $this->hasMany(Pengeluaran::class);
    }

    public function pembiayaan(): HasMany
    {
        return $this->hasMany(Pembiayaan::class);
    }
}
