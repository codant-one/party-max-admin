<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\Product;

class ShoppingCart extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/

    public function client() {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function product() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }


    /**** Public methods ****/

    public static function addCart($request) {

        
        $cart = self::updateOrCreate(
            [
                'client_id' => $request->client_id,
                'product_id' => $request->product_id,
            ],
            ['quantity' => $request->quantity]
        );

        return $cart;
    }


    public static function deleteCart($request) 
    {
        $cart = ShoppingCart::where('client_id', $request->client_id)
                            ->where('product_id', $request->product_id)
                            ->first();
          
        $cart->delete();
    }


}
