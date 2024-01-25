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

    public function product() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**** Public methods ****/

    public static function addFavorite($request) {
        
        $favorite = self::create([
            'user_id' => $request->user_id,
            'product_id' => $request->product_id,
            'date' => now()
        ]);

        return $favorite;
    }


    public static function deleteFavorite($request) 
    {
        $favorite = ProductLike::where('user_id', $request->user_id)
                            ->where('product_id', $request->product_id)
                            ->first();
          
        $favorite->delete();
    }
}
