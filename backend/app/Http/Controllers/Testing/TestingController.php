<?php

namespace App\Http\Controllers\Testing;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\SupplierAccount;


class TestingController extends Controller
{
    public function permissions()
    {
        $permissions = Permission::all();

        return response()->json([
            'success' => true,
            'data' => $permissions
        ], 200);
    }

    public function emails()
    {
        $url = env('APP_DOMAIN').'/register-confirm?&token='.Str::random(60);
        $info = [
            'title' => 'Verificar Correo Electrónico',
            'text' => 'Tu cuenta no está verificada. Confirma tu cuenta con los pasos a seguir para verificarla.',
            'buttonLink' => $url,
            'buttonText' => 'Confirmar'
        ];

        $user = User::find(1);
        
        $data = [
            'title' => $info['title'],
            'user' => $user->name . ' ' . $user->last_name,
            'text' => $info['text'],
            'buttonLink' =>  $info['buttonLink'] ?? null,
            'buttonText' =>  $info['buttonText'] ?? null
        ];

        return view('emails.auth.testing', compact('data'));
    }

    public function minus_stock($orderId)
    {
        $order_details = OrderDetail::with(['product_color'])->where('order_id', $orderId)->get(); 
        
        $productDetails = $order_details->map(function ($detail) {
            return [
                'product_id' => $detail->product_color->product_id,
                'quantity' => $detail->quantity,
            ];
        })->toArray();

        foreach ($productDetails as $item) {
            try {
                $product = Product::find($item['product_id']);
                if ($product) {
                     $product->updateStockProduct($product, $item['quantity']);
                }
                    
            } catch (Exception $e) {
                echo "Error updating stock for product ID: $product->id - " . $e->getMessage() . "<br>";
            }
            
        }

        return response()->json([
            'success' => true,
            'data' => $productDetails
        ], 200);
    }

    public function sum_sales($orderId)
    {
        $order_details = OrderDetail::with(['product_color'])->where('order_id', $orderId)->get();

        $productDetails = $order_details->map(function ($detail) {
            return [
                'product_id' => $detail->product_color->product_id,
                'subtotal' => $detail->total
            ];
        })->toArray();

        foreach ($productDetails as $item) 
        {
            $product = Product::find($item['product_id']);
            $update_sales = SupplierAccount::update_Sales($product->user_id, $item['subtotal']);
        }



        return response()->json([
            'success' => true,
            'total' => $productDetails
        ], 200);
        
    }

}
