<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Order;
use App\Models\ProductColor;
use App\Models\Service;
use App\Models\CakeSize;
use App\Models\Flavor;
use App\Models\Filling;
use App\Models\OrderFile;

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

    public function service()
    {
        return $this->hasOne(Service::class, 'id', 'service_id');
    }

    public function cake_size()
    {
        return $this->hasOne(CakeSize::class, 'id', 'cake_size_id');
    }

    public function flavor()
    {
        return $this->hasOne(Flavor::class, 'id', 'flavor_id');
    }

    public function filling()
    {
        return $this->hasOne(Filling::class, 'id', 'filling_id');
    }

    public function order_file()
    {
        return $this->hasOne(OrderFile::class, 'id', 'order_file_id');
    }
}
