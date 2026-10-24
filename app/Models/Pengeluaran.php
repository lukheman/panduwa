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
        'sumber_dana_id',
        'rekening_anggaran_id',
        'tanggal',
        'keterangan',
        'id_kegiatan',
        'kegiatan_belanja_id',
    ];

    public function sumberDana()
    {
        return $this->belongsTo(SumberDana::class);
    }

    public function rekening()
    {
        return $this->belongsTo(RekeningAnggaran::class, 'rekening_anggaran_id');
    }

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan');
    }

    public function kegiatanBelanja()
    {
        return $this->belongsTo(KegiatanBelanja::class, 'kegiatan_belanja_id');
    }

    public function inventaris()
    {
        return $this->hasOne(Inventaris::class, 'id_pengeluaran');
    }
}
