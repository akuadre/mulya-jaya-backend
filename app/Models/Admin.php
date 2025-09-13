<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'admins';

    // Field yang bisa diisi massal
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // Hidden supaya password ga muncul di JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Casting kalau perlu
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
