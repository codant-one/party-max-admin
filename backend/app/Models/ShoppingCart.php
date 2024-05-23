<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\Client;
use App\Models\ProductColor;

class ShoppingCart extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function client() {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function color() {
        return $this->belongsTo(ProductColor::class, 'product_color_id', 'id');
    }

    /**** Public methods ****/
    public static function addCart($request) {
        $cart = ShoppingCart::updateOrInsert(
            [
                'client_id' => $request->client_id,
                'product_color_id' => $request->product_color_id,
            ],
            [
                'quantity' => $request->quantity,
                'wholesale' => $request->wholesale,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        return $cart;
    }

    public static function deleteCart($request) {
        ShoppingCart::where([
            ['client_id', $request->client_id],
            ['product_color_id', $request->product_color_id]]
        )->delete(); 
    }

    public static function deleteAll($request) {
        ShoppingCart::where('client_id', $request->client_id)->delete(); 
    }


}
