<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventaris extends Model
{
    protected $table = 'inventaris';

    use HasFactory;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'lokasi',
        'tanggal_perolehan',
        'nilai_aset',
        'kondisi',
        'id_pengeluaran',
    ];

    public function pengeluaran()
    {
        return $this->belongsTo(Pengeluaran::class, 'id_pengeluaran');
    }


}