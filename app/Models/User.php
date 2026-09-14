<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    public const TYPE_ADMIN = 1;

    public const TYPE_STAFF = 2;

    protected $fillable = [
        'name',
        'username',
        'password',
        'type',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];
}
