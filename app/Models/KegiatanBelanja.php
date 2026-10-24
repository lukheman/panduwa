<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KegiatanBelanja extends Model
{
    use HasFactory;

    protected $table = 'kegiatan_belanja';

    protected $fillable = [
        'kegiatan_id',
        'rekening_anggaran_id',
        'kode_rekening',
        'uraian',
        'anggaran',
    ];

    protected $casts = ['anggaran' => 'decimal:2'];

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(RekeningAnggaran::class, 'rekening_anggaran_id');
    }

    public function pengeluarans(): HasMany
    {
        return $this->hasMany(Pengeluaran::class, 'kegiatan_belanja_id');
    }
}
