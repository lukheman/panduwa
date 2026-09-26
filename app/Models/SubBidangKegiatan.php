<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubBidangKegiatan extends Model
{
    protected $table = 'sub_bidang_kegiatan';

    use HasFactory;

    protected $fillable = [
        'bidang_kegiatan_id',
        'kode',
        'nama',
        'deskripsi',
    ];

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(BidangKegiatan::class, 'bidang_kegiatan_id');
    }

    public function kegiatans(): HasMany
    {
        return $this->hasMany(Kegiatan::class, 'sub_bidang_kegiatan_id');
    }

    public function getNamaLengkapAttribute(): string
    {
        return $this->bidang?->nama.' - '.$this->nama;
    }
}
