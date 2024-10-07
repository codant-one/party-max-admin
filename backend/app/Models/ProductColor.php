<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductColor extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function categories()
    {
        return $this->hasMany(ProductCategory::class, 'product_color_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_color_id');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id')->withTrashed();
    }

    public function orders()
    {
        return $this->hasMany(OrderDetail::class, 'product_color_id');
    }

}
