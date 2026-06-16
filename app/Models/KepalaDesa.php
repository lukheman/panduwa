<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Traits\HasProfilePhoto;

class KepalaDesa extends Authenticatable
{
    protected $table = 'kepala_desa';

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
}