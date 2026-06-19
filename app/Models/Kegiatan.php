<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    protected $table = 'kegiatan';

    use HasFactory;

    protected $fillable = [
        'nama_kegiatan',
        'lokasi',
        'anggaran',
        'status',
        'foto_progres',
    ];

    protected $casts = [
        'status' => \App\Enums\StatusKegiatan::class,
    ];

    public function pengeluarans()
    {
        return $this->hasMany(Pengeluaran::class, 'id_kegiatan');
    }
}