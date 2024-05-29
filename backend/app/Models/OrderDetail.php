<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Order;
use App\Models\ProductColor;

class OrderDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    /**** Relationship ****/

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');

    }

    public function product_color()
    {
        return $this->hasOne(ProductColor::class, 'id', 'product_color_id');
    }
}
