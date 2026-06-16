<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiAset extends Model
{
    protected $table = 'mutasi_aset';

    use HasFactory;

    protected $fillable = [
        'id_inventaris',
        'jenis_mutasi',
        'tanggal',
        'keterangan',
        'id_bendahara',
    ];

    public function inventaris()
    {
        return $this->belongsTo(Inventaris::class, 'id_inventaris');
    }

    public function bendahara()
    {
        return $this->belongsTo(Bendahara::class, 'id_bendahara');
    }
}