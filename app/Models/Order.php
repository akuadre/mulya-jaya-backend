<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'product_id', 'address', 'order_date', 'total_price', 'status', 'photo', 'payment_method'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function lense()
    {
        return $this->hasOne(Lense::class);
    }
}
