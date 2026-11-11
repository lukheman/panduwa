<?php

namespace App\Models;

use App\Traits\FilterBySumberDana;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemasukan extends Model
{
    protected $table = 'pemasukan';

    use FilterBySumberDana, HasFactory;

    protected $fillable = [
        'sumber_dana',
        'sumber_dana_id',
        'rekening_anggaran_id',
        'jumlah',
        'anggaran',
        'tanggal',
        'keterangan',
    ];

    public function sumberDana()
    {
        return $this->belongsTo(SumberDana::class);
    }

    public function rekening()
    {
        return $this->belongsTo(RekeningAnggaran::class, 'rekening_anggaran_id');
    }
}
