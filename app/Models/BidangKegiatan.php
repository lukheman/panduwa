<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class BidangKegiatan extends Model
{
    protected $table = 'bidang_kegiatan';

    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
    ];

    public function subBidangs(): HasMany
    {
        return $this->hasMany(SubBidangKegiatan::class, 'bidang_kegiatan_id');
    }

    public function kegiatans(): HasManyThrough
    {
        return $this->hasManyThrough(
            Kegiatan::class,
            SubBidangKegiatan::class,
            'bidang_kegiatan_id',
            'sub_bidang_kegiatan_id'
        );
    }
}
