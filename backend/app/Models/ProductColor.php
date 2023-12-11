<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\Color;
use App\Models\Product;

class ProductColor extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function color()
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    

}
