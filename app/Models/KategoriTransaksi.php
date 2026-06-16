<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriTransaksi extends Model
{
    protected $table = 'kategori_transaksi';

    use HasFactory;

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
    ];

    public function pengeluarans()
    {
        return $this->hasMany(Pengeluaran::class, 'id_kategori_transaksi');
    }
}