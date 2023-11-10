<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function categories()
    {
        return $this->hasMany(ProductCategory::class, 'product_image_id');
    }

}
