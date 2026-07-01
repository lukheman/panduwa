<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';

    use HasFactory;

    protected $fillable = [

        'jumlah',
        'tanggal',
        'keterangan',
        'id_kegiatan',
    ];


    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan');
    }

    public function inventaris()
    {
        return $this->hasOne(Inventaris::class, 'id_pengeluaran');
    }
}