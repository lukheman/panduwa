<?php

namespace App\Models;

use App\Enums\KondisiInventaris;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventaris extends Model
{
    protected $table = 'inventaris';

    use HasFactory;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'tanggal_perolehan',
        'nilai_aset',
        'kondisi',
        'id_pengeluaran',
    ];

    protected $casts = [
        'kondisi' => KondisiInventaris::class,
    ];

    public function pengeluaran()
    {
        return $this->belongsTo(Pengeluaran::class, 'id_pengeluaran');
    }
}
