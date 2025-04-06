<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class QuoteDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

     /**** Relationship ****/
     public function quote()
     {
         return $this->belongsTo(Quote::class, 'order_id', 'id');
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
}
