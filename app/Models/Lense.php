<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lense extends Model
{
    use HasFactory;

    // Izinkan semua kolom ini untuk diisi secara massal
    protected $fillable = [
        'order_id',
        'lense_type',
        'is_custom',
        'left_sph',
        'left_cyl',
        'left_axis',
        'right_sph',
        'right_cyl',
        'right_axis',
        'pd',
    ];

    /**
     * Mendefinisikan relasi bahwa Lense ini milik sebuah Order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
