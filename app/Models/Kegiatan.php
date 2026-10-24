<?php

namespace App\Models;

use App\Enums\StatusKegiatan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kegiatan extends Model
{
    protected $table = 'kegiatan';

    use HasFactory;

    protected $fillable = [
        'nama_kegiatan',
        'kode_rekening',
        'sumber_dana_id',
        'lokasi',
        'sub_bidang_kegiatan_id',
        'rencana_anggaran',
        'realisasi_anggaran',
        'status',
        'foto_progres',
    ];

    protected $casts = [
        'status' => StatusKegiatan::class,
    ];

    public function subBidang(): BelongsTo
    {
        return $this->belongsTo(SubBidangKegiatan::class, 'sub_bidang_kegiatan_id');
    }

    public function pengeluarans()
    {
        return $this->hasMany(Pengeluaran::class, 'id_kegiatan');
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class);
    }

    public function belanjaDetails()
    {
        return $this->hasMany(KegiatanBelanja::class);
    }
}
