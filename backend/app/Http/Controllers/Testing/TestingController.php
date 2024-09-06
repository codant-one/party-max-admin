<?php

namespace App\Http\Controllers\Testing;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\OrderDetail;
use App\Models\Order;
use App\Models\Product;
use App\Models\SupplierAccount;

class TestingController extends Controller
{
    public function permissions() {
        $permissions = Permission::all();

        return response()->json([
            'success' => true,
            'data' => $permissions
        ], 200);
    }

    public function emails() {
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

    public function confirmationOrderPayU() {

        $orderId = 7;
        $message = 'Transacción rechazada por entidad financiera.';
        $payment_state_id = 3;

        $order =  Order::with(['client.user.userDetail', 'billing'])->find($orderId); 

        if($order->client) {
            $user = $order->client->user->name . ' ' . $order->client->user->last_name;
        } else {
            $user = $order->billing->name . ' ' . $order->billing->last_name;
        }

        $data = [
            'orderId' => $order->id,
            'user' => $user,
            'message' => $message,
            'payment_state_id' => $payment_state_id === 3 ? 'FALLIDA' : 'CANCELADA'
        ];

        return view('emails.payment.failed', compact('data'));
    }

    public function paymentSummaryEmail() {

        $orderId = 7;

        $order = 
            Order::with([
                'billing', 
                'details.product_color.product', 
                'address.province', 
                'client.user.userDetail'
            ])->find($orderId); 

        $link_send = env('APP_DOMAIN').'/detail-purchases/'.$orderId;
        $link_purchases = env('APP_DOMAIN').'/purchases';

        $note = is_null($order->billing->note) ? '.' : '. (' . $order->billing->note . ').';        

        if($order->client) {
            $user = $order->client->user->name . ' ' . $order->client->user->last_name;
            $phone = $order->client->user->userDetail->phone;
            $email = $order->client->user->email;

            $address = 
                $order->address->address . ', ' . 
                $order->address->street . ', ' . 
                $order->address->city . ', ' . 
                $order->address->postal_code . ', ' . 
                $order->address->province->name .
                $note;
        } else {
            $user = $order->billing->name . ' ' . $order->billing->last_name;
            $phone = $order->shipping_phone;
            $email = $order->billing->email;

            $address = 
                $order->shipping_address . ', ' . 
                $order->shipping_street . ', ' . 
                $order->shipping_city . ', ' . 
                $order->shipping_postal_code . ', ' . 
                $order->province->name .
                $note;
        }

        $payment_method = 
            ($order->billing->pse === 0) ? 
                $order->billing->payment_method_name . ' terminada en ' . $order->billing->card_number: 
                'PSE';

        $products = [];

        foreach ($order->details as $detail) {
            $productInfo = [
                'product_id' => $detail->product_color->product->id,
                'product_name' => $detail->product_color->product->name,
                'product_image' => asset('storage/' . $detail->product_color->product->image),
                'color' => $detail->product_color->color->name,
                'slug' =>env('APP_DOMAIN').'/products/'.$detail->product_color->product->slug,
                'quantity' => $detail->quantity,
                'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
            ];
            
            array_push($products, $productInfo);
        
        }
        //dd($order);
        $data = [
            'address' => $address,
            'user' => $user,
            'phone' => $phone,
            'total' => $order->total,
            'payment_method' => $payment_method,
            'products' => $products,
            'link_send' => $link_send,
            'link_purchases' => $link_purchases,
            'showButton' => $order->client ? true : false
        ];

        //dd($data);
        return view('emails.payment.purchase_detail', compact('data'));
    }

    public function productSale() {

        $orderId = 15;

        $order = 
            Order::with([
                'details.product_color.product.user'
            ])->find($orderId); 

        $link_send = env('APP_DOMAIN_ADMIN').'/dashboard/admin/orders/'.$orderId;
        $products = [];

        foreach ($order->details as $detail) {
            $email = $detail->product_color->product->user->email;

            $productInfo = [
                'email' => $email,
                'product_id' => $detail->product_color->product->id,
                'product_name' => $detail->product_color->product->name,
                'product_image' => asset('storage/' . $detail->product_color->product->image),
                'color' => $detail->product_color->color->name,
                'slug' =>env('APP_DOMAIN').'/products/'.$detail->product_color->product->slug,
                'quantity' => $detail->quantity,
                'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
            ];
            
            if (!isset($products[$email])) {
                $products[$email] = [];
            }

            $products[$email][] = $productInfo;
        
        }

        ksort($products);
       
        // foreach($products as $key => $item) {
        //     dd($item);
        // }
        // dd($products);

        $data = [
            'total' => $order->total,
            'products' => $products[$email],
            'link_send' => $link_send,
            'showButton' => true
        ];

        return view('emails.payment.product_sale', compact('data'));
    }

    public function infoOrder() {

        $orderId = 16;

        $order = 
            Order::with([
                'billing', 
                'details.product_color.product', 
                'address.province', 
                'client.user.userDetail'
            ])->find($orderId); 

        $link_send = env('APP_DOMAIN_ADMIN').'/dashboard/admin/orders/'.$orderId;

        $note = is_null($order->billing->note) ? '.' : '. (' . $order->billing->note . ').';        

        if($order->client) {
            $user = $order->client->user->name . ' ' . $order->client->user->last_name;
            $phone = $order->client->user->userDetail->phone;
            $email = $order->client->user->email;

            $address = 
                $order->address->address . ', ' . 
                $order->address->street . ', ' . 
                $order->address->city . ', ' . 
                $order->address->postal_code . ', ' . 
                $order->address->province->name .
                $note;
        } else {
            $user = $order->billing->name . ' ' . $order->billing->last_name;
            $phone = $order->shipping_phone;
            $email = $order->billing->email;

            $address = 
                $order->shipping_address . ', ' . 
                $order->shipping_street . ', ' . 
                $order->shipping_city . ', ' . 
                $order->shipping_postal_code . ', ' . 
                $order->province->name .
                $note;
        }

        $payment_method = 
            ($order->billing->pse === 0) ? 
                $order->billing->payment_method_name . ' terminada en ' . $order->billing->card_number: 
                'PSE';

        $products = [];

        foreach ($order->details as $detail) {
            $productInfo = [
                'product_id' => $detail->product_color->product->id,
                'product_name' => $detail->product_color->product->name,
                'product_image' => asset('storage/' . $detail->product_color->product->image),
                'color' => $detail->product_color->color->name,
                'slug' =>env('APP_DOMAIN').'/products/'.$detail->product_color->product->slug,
                'quantity' => $detail->quantity,
                'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
            ];
            
            array_push($products, $productInfo);
        
        }
        //dd($order);
        $data = [
            'address' => $address,
            'user' => $user,
            'phone' => $phone,
            'total' => $order->total,
            'payment_method' => $payment_method,
            'products' => $products,
            'link_send' => $link_send,
            'showButton' => $order->client ? true : false
        ];

        //dd($data);
        return view('emails.payment.info_order', compact('data'));
    }

    public function littleProductExistenceEmail() {

        $productId = 16;

        $product = Product::with(['colors.product', 'user'])->find($productId); 
        $link = env('APP_DOMAIN_ADMIN').'/dashboard/products/products';

        $productInfo = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_image' => asset('storage/' . $product->image),
            'slug' =>env('APP_DOMAIN_ADMIN').'/dashboard/products/products/edit/'.$product->id,
            'stock' => $product->stock . ((intval($product->stock) > 1) ? ' Unidades' : ' Unidad'),
        ];

        $data = [
            'product' => $productInfo,
            'link' => $link
        ];

        //dd($data);
        return view('emails.suppliers.little_product_existence', compact('data'));
    }

    public function outOfStockEmail() {

        $productId = 16;

        $product = Product::with(['colors.product', 'user'])->find($productId); 
        $link = env('APP_DOMAIN_ADMIN').'/dashboard/products/products';

        $productInfo = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_image' => asset('storage/' . $product->image),
            'slug' =>env('APP_DOMAIN_ADMIN').'/dashboard/products/products/edit/'.$product->id,
            'stock' => $product->stock . ((intval($product->stock) > 1) ? ' Unidades' : ' Unidad'),
        ];

        $data = [
            'product' => $productInfo,
            'link' => $link
        ];

        //dd($data);
        return view('emails.suppliers.out_of_stock', compact('data'));
    }

    public function sendOrder() {

        $orderId = 7;
        $shipping_state_id = 3;
        $reason = 'no quise';

        $order = 
            Order::with([
                'billing', 
                'details.product_color.product', 
                'address.province', 
                'client.user.userDetail'
            ])->find($orderId); 

        $link = env('APP_DOMAIN');
        $link_contact = env('APP_DOMAIN').'/about-us';
        $note = is_null($order->billing->note) ? '.' : '. (' . $order->billing->note . ').';

        if($order->client) {
            $user = $order->client->user->name . ' ' . $order->client->user->last_name;
            $email = $order->client->user->email;

            $address = 
                $order->address->address . ', ' . 
                $order->address->street . ', ' . 
                $order->address->city . ', ' . 
                $order->address->postal_code . ', ' . 
                $order->address->province->name .
                $note;
        } else {
            $user = $order->billing->name . ' ' . $order->billing->last_name;
            $email = $order->billing->email;

            $address = 
                $order->shipping_address . ', ' . 
                $order->shipping_street . ', ' . 
                $order->shipping_city . ', ' . 
                $order->shipping_postal_code . ', ' . 
                $order->province->name .
                $note;
        }

        $products = [];

        foreach ($order->details as $detail) {
            $productInfo = [
                'product_id' => $detail->product_color->product->id,
                'product_name' => $detail->product_color->product->name,
                'product_image' => asset('storage/' . $detail->product_color->product->image),
                'color' => $detail->product_color->color->name,
                'slug' =>env('APP_DOMAIN').'/products/'.$detail->product_color->product->slug,
                'quantity' => $detail->quantity,
                'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
            ];
            
            array_push($products, $productInfo);
        }

        switch ($shipping_state_id) {
            case '2':
                $title = 'Pedido no entregado';
                $text = 'No se ha podido enviar su pedido.';
                $subject = 'Pedido fuera de entrega.';
                break;
            case '3':
                $title = 'Llegó tu compra';
                $text = 'Hicimos entrega de tu producto en ';
                $subject = 'Tu pedido ha sido entregado.';
                break;
            case '4':
                $title = 'Pedido enviado';
                $text = 'Enviamos tu pedido a ';
                $subject = 'Tu pedido ha sido enviado.';
                break;
            default:
                $title = 'Pedido enviado';
                $text = 'Enviamos tu pedido a ';
                $subject = 'Tu pedido ha sido enviado.';
        }

        $data = [
            'address' => $address,
            'user' => $user,
            'products' => $products,
            'link' => $link,
            'link_contact' => $link_contact,
            'title' => $title,
            'text' => $text,
            'reason' => $reason,
            'shipping_state_id' => $shipping_state_id
        ];

        // dd($data);
        return view('emails.clients.send_orders', compact('data'));
    }

    public function sendInfo() {

        $data = [
            'name' => 'Steffani Castro',
            'nit' => '4978824',
            'email' => 'steffani.castro.useche@gmail.com',
            'phone' => '3042603376'
        ];

        return view('emails.suppliers.send_info', compact('data'));
    }

    public function minus_stock($orderId) {
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

    public function sum_sales($orderId) {
        $order_details = OrderDetail::with(['product_color'])->where('order_id', $orderId)->get();

        $productDetails = $order_details->map(function ($detail) {
            return [
                'product_id' => $detail->product_color->product_id,
                'subtotal' => $detail->total
            ];
        })->toArray();

        foreach ($productDetails as $item) {
            $product = Product::find($item['product_id']);
            $update_sales = SupplierAccount::updateSales($product->user_id, $item['subtotal']);
        }

        return response()->json([
            'success' => true,
            'total' => $productDetails
        ], 200);
        
    }

}
