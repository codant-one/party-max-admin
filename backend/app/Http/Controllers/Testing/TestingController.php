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
use App\Models\Quote;
use App\Models\ProductColor;

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

        $orderId = 58;

        $order = 
            Order::with([
                'billing', 
                'details.product_color.product', 
                'details.service',
                'details.cake_size',
                'details.flavor',
                'details.filling',
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
        $services = [];

        foreach ($order->details as $detail) {
            if($detail->product_color) {
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
            } else {
                $serviceInfo = [
                    'service_id' => $detail->service->id,
                    'service_name' => $detail->service->name,
                    'service_is_full' => $detail->service->is_full,
                    'service_image' => asset('storage/' . $detail->service->image),
                    'flavor' => $detail->flavor->name,
                    'filling' => $detail->filling->name,
                    'cake_size' => $detail->cake_size->name,
                    'slug' =>env('APP_DOMAIN').'/services/'.$detail->service->slug,
                    'quantity' => $detail->quantity,
                    'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
                ];
                
                array_push($services, $serviceInfo);
            }
        }
        // dd($order);
        $data = [
            'address' => $address,
            'user' => $user,
            'phone' => $phone,
            'total' => $order->total,
            'payment_method' => $payment_method,
            'products' => $products,
            'services' => $services,
            'link_send' => $link_send,
            'link_purchases' => $link_purchases,
            'showButton' => $order->client ? true : false
        ];

        //dd($data);
        return view('emails.payment.purchase_detail', compact('data'));
    }

    public function productSale() {

        $orderId = 58;

        $order = 
            Order::with([
                'details.product_color.product.user',
                'details.service',
                'details.cake_size',
                'details.flavor',
                'details.filling',
            ])->find($orderId); 

        $link_send = env('APP_DOMAIN_ADMIN').'/dashboard/admin/orders/'.$orderId;      
        $products = [];
        $services = [];

        foreach ($order->details as $detail) {

            if($detail->product_color) {
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
            } else {
                $email = $detail->service->user->email;

                $serviceInfo = [
                    'email' => $email,
                    'service_id' => $detail->service->id,
                    'service_name' => $detail->service->name,
                    'service_is_full' => $detail->service->is_full,
                    'service_image' => asset('storage/' . $detail->service->image),
                    'flavor' => $detail->flavor->name,
                    'filling' => $detail->filling->name,
                    'cake_size' => $detail->cake_size->name,
                    'slug' =>env('APP_DOMAIN').'/services/'.$detail->service->slug,
                    'quantity' => $detail->quantity,
                    'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
                ];

                if (!isset($services[$email])) {
                    $services[$email] = [];
                }

                $services[$email][] = $serviceInfo;
            }
        }

        ksort($products);
        ksort($services);
        // foreach($products as $key => $item) {
        //     dd($item);
        // }
        // dd($products);

        $data = [
            'total' => $order->total,
            'products' => count($products) > 0 ? $products[$email] : [],
            'services' => count($services) > 0 ? $services[$email] : [],
            'link_send' => $link_send,
            'showButton' => true
        ];

        return view('emails.payment.product_sale', compact('data'));
    }

    public function infoOrder() {

        $orderId = 58;

        $order = 
            Order::with([
                'billing', 
                'details.product_color.product', 
                'details.service',
                'details.cake_size',
                'details.flavor',
                'details.filling',
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
        $services = [];

        foreach ($order->details as $detail) {
            if($detail->product_color) {
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
            } else {
                $serviceInfo = [
                    'service_id' => $detail->service->id,
                    'service_name' => $detail->service->name,
                    'service_is_full' => $detail->service->is_full,
                    'service_image' => asset('storage/' . $detail->service->image),
                    'flavor' => $detail->flavor->name,
                    'filling' => $detail->filling->name,
                    'cake_size' => $detail->cake_size->name,
                    'slug' =>env('APP_DOMAIN').'/services/'.$detail->service->slug,
                    'quantity' => $detail->quantity,
                    'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
                ];
                
                array_push($services, $serviceInfo);
            }
        
        }
        //dd($order);
        $data = [
            'address' => $address,
            'user' => $user,
            'phone' => $phone,
            'total' => $order->total,
            'payment_method' => $payment_method,
            'products' => $products,
            'services' => $services,
            'link_send' => $link_send,
            'showButton' => $order->client ? true : false
        ];

        //dd($data);
        return view('emails.payment.info_order', compact('data'));
    }

    public function littleProductExistenceEmail() {

        $productColorId = 2749;
        $product_color = ProductColor::with(['product', 'color'])->find($productColorId);

        $link = env('APP_DOMAIN_ADMIN').'/dashboard/products/products';

        $productInfo = [
            'product_id' => $product_color->product->id,
            'product_name' => $product_color->product->name,
            'product_color' => $product_color->color->name,
            'product_image' => asset('storage/' . $product_color->product->image),
            'slug' =>env('APP_DOMAIN_ADMIN').'/dashboard/products/products/edit/'.$product_color->product->id,
            'stock' => $product_color->stock . ((intval($product_color->stock) > 1) ? ' Unidades' : ' Unidad'),
        ];

        $data = [
            'product' => $productInfo,
            'link' => $link
        ];

        return view('emails.suppliers.little_product_existence', compact('data'));
    }

    public function outOfStockEmail() {

        $productColorId = 2749;
        $product_color = ProductColor::with(['product', 'color'])->find($productColorId);

        $link = env('APP_DOMAIN_ADMIN').'/dashboard/products/products';

        $productInfo = [
            'product_id' => $product_color->product->id,
            'product_name' => $product_color->product->name,
            'product_color' => $product_color->color->name,
            'product_image' => asset('storage/' . $product_color->product->image),
            'slug' =>env('APP_DOMAIN_ADMIN').'/dashboard/products/products/edit/'.$product_color->product->id,
            'stock' => $product_color->stock . ((intval($product_color->stock) > 1) ? ' Unidades' : ' Unidad'),
        ];

        $data = [
            'product' => $productInfo,
            'link' => $link
        ];

        //dd($data);
        return view('emails.suppliers.out_of_stock', compact('data'));
    }

    public function sendOrder() {

        $orderId = 60;
        $shipping_state_id = 3;
        $reason = 'no quise';

        $order = 
            Order::with([
                'billing', 
                'details.product_color.product', 
                'details.service',
                'details.cake_size',
                'details.flavor',
                'details.filling',
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
        $services = [];

        foreach ($order->details as $detail) {
            if($detail->product_color) {
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
            } else {
                $serviceInfo = [
                    'service_id' => $detail->service->id,
                    'service_name' => $detail->service->name,
                    'service_is_full' => $detail->service->is_full,
                    'service_image' => asset('storage/' . $detail->service->image),
                    'flavor' => $detail->flavor->name,
                    'filling' => $detail->filling->name,
                    'cake_size' => $detail->cake_size->name,
                    'slug' =>env('APP_DOMAIN').'/services/'.$detail->service->slug,
                    'quantity' => $detail->quantity,
                    'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
                ];
                
                array_push($services, $serviceInfo);
            }
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
                'product_color_id' => $detail->product_color_id,
                'product_id' => $detail->product_color ? $detail->product_color->product_id : 0,
                'quantity' => $detail->quantity,
                'total' => $detail->tota
            ];
        })->toArray();

        foreach ($productDetails as $item) {
            try {
                $product = Product::with(['colors.product', 'user'])->find($item['product_id']);
                if ($product) {
                    $product->updateStockProduct($item);
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

    public function sendEvaluation() {

        $orderId = 65;

        $order = 
            Order::with([
                'billing', 
                'details' => function ($query) {
                    $query->where('is_rating', 0);
                },
                'details.product_color.product', 
                'details.service',
                'client.user.userDetail'
            ])->find($orderId); 

        $link = env('APP_DOMAIN').'/register/form_client';
        // $link = env('APP_DOMAIN').'/detail-purchases/'.$orderId;

        if($order->client) {
            $user = $order->client->user->name . ' ' . $order->client->user->last_name;
            $email = $order->client->user->email;
        } else {
            $user = $order->billing->name . ' ' . $order->billing->last_name;
            $email = $order->billing->email;
        }

        $products = [];
        $services = [];

        foreach ($order->details as $detail) {
            if($detail->product_color) {
                $productInfo = [
                    'product_id' => $detail->product_color->product->id,
                    'product_name' => $detail->product_color->product->name,
                    'product_image' => asset('storage/' . $detail->product_color->product->image),
                    'slug' =>env('APP_DOMAIN').'/products/'.$detail->product_color->product->slug,
                ];
                
                array_push($products, $productInfo);
            } else {
                $serviceInfo = [
                    'service_id' => $detail->service->id,
                    'service_name' => $detail->service->name,
                    'service_is_full' => $detail->service->is_full,
                    'service_image' => asset('storage/' . $detail->service->image),
                    'slug' =>env('APP_DOMAIN').'/services/'.$detail->service->slug,
                ];
                
                array_push($services, $serviceInfo);
            }
        }
        $text = '¡Gracias por tu compra, <strong>'.$user.'</strong>!<br>';
        $text .= 'Los productos que elegiste están listos para hacer de tu fiesta un momento especial. ';
        $text .= 'Tu calificación nos ayuda a mejorar y brindarte un mejor servicio. ';
        $text .= 'Para dejar tu opinión, solo necesitas registrarte en nuestra plataforma y acceder a promociones exclusivas que pronto tendremos para ti.<br>';
        $text .= 'Registrarte ¡Es rápido y fácil!<br>';
        $text2 = '¡Esperamos tu valoración!<br>Saludos';

        $buttonText = 'Regístrate y califica aquí';

        // ----------------------------------------
        // $text = '¡Gracias por tu compra, <strong>'.$user.'</strong>!<br>';
        // $text .= 'Esperamos que los productos que elegiste hayan hecho de tu fiesta un momento inolvidable. Ahora, nos encantaría conocer tu experiencia. ';
        // $text .= 'Tu opinión es clave para ayudarnos a mejorar y ofrecerte un mejor servicio. ';
        // $text .= '<strong>Tómate un minuto y déjanos tu valoración</strong>. Además, al calificar, seguirás accediendo a promociones y beneficios exclusivos.';

        // $text2 = '¡Gracias por ser parte de nuestra comunidad!<br>Saludos';

        // $buttonText = 'Califica ahora';
        // ----------------------------------------

        // $register = env('APP_DOMAIN').'/register/form_client';
        // $text = 'Hola <strong>'.$user.'</strong>,<br>';
        // $text .= 'Tus compras ayudaron a crear momentos increíbles. Y ahora puedes ayudarnos a crear aún más. ¡Tu opinión es clave!✨. <br> ';
        // $text .= 'Al dejar tu calificación, no solo guías a otros para que elijan los mejores productos para sus fiestas, sino que también <strong>haces que la celebración sea aún más grande⭐. </strong>';
        // $text .= 'Además, al calificarnos, <strong>seguirás disfrutando</strong> de promociones y beneficios exclusivos.<br><br>';
        // $text .= '🔹 <strong>¿Aún no estás registrado?</strong><br>';
        // $text .= 'Para dejar tu evaluación y acceder a estos beneficios, solo necesitas registrarte en nuestro sistema. ¡Es rápido y fácil!<br>';
        // $text .= "<a href='$register' target='_blank'>Haz clic aquí para registrarte</a><br>";
        // $text2 = '¡Gracias por ser parte de nuestra comunidad!<br>Saludos';

        // $buttonText = 'Califica ahora';
        $data = [
            'products' => $products,
            'services' => $services,
            'link' => $link,
            'title' => '¿Qué te pareció tu producto? ',
            'text' => $text,
            'text2' => $text2,
            'buttonText' => $buttonText
        ];

        return view('emails.clients.send_evaluation', compact('data'));
    }

    public function sendSurvey() {

        $orderId = 65;

        $order = 
            Order::with([
                'billing',
                'client.user.userDetail'
            ])->find($orderId); 

        $link = 'https://docs.google.com/forms/d/1m3TVPc3rD2ECSnx4B2A6ZQqkCxFwdN-NSFR-UNcEJ5A/edit';

        if($order->client) {
            $user = $order->client->user->name . ' ' . $order->client->user->last_name;
            $email = $order->client->user->email;
        } else {
            $user = $order->billing->name . ' ' . $order->billing->last_name;
            $email = $order->billing->email;
        }

        $text = 'Hola <strong>'.$user.'</strong>,<br>';
        $text .= 'Gracias por confiar en PartyMax. Queremos asegurarnos de que tu experiencia de compra haya sido excelente y, para ello, tu opinión es clave. ';
        $text .= 'Te invitamos a completar nuestra encuesta de satisfacción. Solo te tomará menos de 2 minutos y nos ayudará a seguir mejorando para ofrecerte el mejor servicio posible.';

        $text2 = 'Valoramos mucho tu tiempo y tu opinión.<br> ¡Gracias por ayudarnos a mejorar!';

        $buttonText = '✏️ Responder encuesta';

        $data = [
            'link' => $link,
            'title' => '¿Nos ayudarías con tu opinión?',
            'text' => $text,
            'text2' => $text2,
            'buttonText' => $buttonText
        ];

        return view('emails.clients.send_survey', compact('data'));
    }

    public function pdfs() {

        $quote = Quote::with(
            'document_type',
            'details.product_color',
            'details.service',
            'details.cake_size',
            'details.flavor',
            'details.filling'
        )->find(1);

        $products = [];
        $services = [];

        foreach ($quote->details as $detail) {
            if($detail->product_color) {
                $productInfo = [
                    'product_id' => $detail->product_color->product->id,
                    'product_name' => $detail->product_color->product->name,
                    'product_price' => $detail->price,
                    'product_total' => $detail->total * $detail->quantity,
                    'product_image' => asset('storage/' . $detail->product_color->product->image),
                    'color' => $detail->product_color->color->name,
                    'slug' =>env('APP_DOMAIN').'/products/'.$detail->product_color->product->slug,
                    'quantity' => $detail->quantity,
                    'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
                ];
                
                array_push($products, $productInfo);
            } else {
                $serviceInfo = [
                    'service_id' => $detail->service->id,
                    'service_name' => $detail->service->name,
                    'service_is_full' => $detail->service->is_full,
                    'service_price' => $detail->price,
                    'service_total' => $detail->total *  $detail->quantity,
                    'service_image' => asset('storage/' . $detail->service->image),
                    'flavor' => $detail->flavor->name,
                    'filling' => $detail->filling->name,
                    'cake_size' => $detail->cake_size->name,
                    'slug' =>env('APP_DOMAIN').'/services/'.$detail->service->slug,
                    'quantity' => $detail->quantity,
                    'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
                ];
                
                array_push($services, $serviceInfo);
            }
        }

        return view('pdfs.quote', compact('quote', 'products', 'services') );
    }

}
