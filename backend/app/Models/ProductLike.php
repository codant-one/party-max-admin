<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Product;

class ProductLike extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function product() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**** Public methods ****/
    public static function addFavorite($request) {
        
        $favorite = ProductLike::updateOrInsert(
            [
                'user_id' => $request->user_id,
                'product_id' => $request->product_id
            ],
            [   
                'date' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        return $favorite;
    }

    public static function deleteFavorite($request) {

        ProductLike::where([
            ['user_id', $request->user_id],
            ['product_id', $request->product_id]]
        )->delete(); 
    }
   
}
