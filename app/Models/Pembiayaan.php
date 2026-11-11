<?php

namespace App\Models;

use App\Traits\FilterBySumberDana;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembiayaan extends Model
{
    use FilterBySumberDana, HasFactory;

    protected $table = 'pembiayaan';

    protected $fillable = ['jenis', 'rekening_anggaran_id', 'sumber_dana_id', 'uraian', 'anggaran', 'realisasi', 'tanggal', 'keterangan'];

    protected $casts = ['anggaran' => 'decimal:2', 'realisasi' => 'decimal:2', 'tanggal' => 'date'];

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(RekeningAnggaran::class, 'rekening_anggaran_id');
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class, 'sumber_dana_id');
    }
}
