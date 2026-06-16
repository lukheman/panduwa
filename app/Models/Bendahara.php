<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Traits\HasProfilePhoto;

class Bendahara extends Authenticatable
{
    protected $table = 'bendahara';

    use HasFactory, HasProfilePhoto;

    protected $fillable = [
        'nama',
        'email',
        'password',
        'avatar',
    ];

    protected $hidden = [
        'password',
    ];



    public function mutasiAsets()
    {
        return $this->hasMany(MutasiAset::class, 'id_bendahara');
    }
}