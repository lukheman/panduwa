<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Traits\HasProfilePhoto;

class Admin extends Authenticatable
{
    protected $table = 'admin';

    use HasFactory, HasProfilePhoto;

    protected $primaryKey = 'id_admin';

    protected $fillable = [
        'email',
        'password',
        'nama',
        'avatar',
    ];

    protected $hidden = [
        'password',
    ];
}