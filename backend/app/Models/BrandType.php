<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Brand;

class BrandType extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
     public function brands() {
        return $this->hasMany(Brand::class, 'brand_type_id', 'id');
    }

}
