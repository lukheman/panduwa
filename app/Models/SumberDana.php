<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SumberDana extends Model
{
    use HasFactory;

    public const KODE_DANA_DESA = 'DDS';

    public const KODE_ALOKASI_DANA_DESA = 'ADD';

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

    public static function normalisasiKode(?string $kode): string
    {
        return in_array($kode, [self::KODE_DANA_DESA, self::KODE_ALOKASI_DANA_DESA], true)
            ? $kode
            : self::KODE_DANA_DESA;
    }

    public static function labelUntukKode(?string $kode): string
    {
        return match (self::normalisasiKode($kode)) {
            self::KODE_ALOKASI_DANA_DESA => 'Alokasi Dana Desa',
            default => 'Dana Desa',
        };
    }
}
