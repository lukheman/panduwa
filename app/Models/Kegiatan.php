<?php

namespace App\Models;

use App\Enums\KelompokKegiatan;
use App\Enums\StatusKegiatan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    protected $table = 'kegiatan';

    use HasFactory;

    protected $fillable = [
        'nama_kegiatan',
        'lokasi',
        'kelompok',
        'rencana_anggaran',
        'realisasi_anggaran',
        'status',
        'foto_progres',
    ];

    protected $casts = [
        'kelompok' => KelompokKegiatan::class,
        'status' => StatusKegiatan::class,
    ];

    public function pengeluarans()
    {
        return $this->hasMany(Pengeluaran::class, 'id_kegiatan');
    }
}
