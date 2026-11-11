<?php

namespace App\Models;

use App\Enums\KondisiInventaris;
use App\Traits\FilterBySumberDana;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventaris extends Model
{
    protected $table = 'inventaris';

    use FilterBySumberDana, HasFactory;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'jumlah',
        'tanggal_perolehan',
        'nilai_aset',
        'kondisi',
        'sumber_dana_id',
        'id_pengeluaran',
        'keterangan',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'kondisi' => KondisiInventaris::class,
    ];

    public function pengeluaran(): BelongsTo
    {
        return $this->belongsTo(Pengeluaran::class, 'id_pengeluaran');
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class);
    }
}
