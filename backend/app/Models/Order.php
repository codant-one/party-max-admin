<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Models\OrderDetail;
use App\Models\ShippingState; 
use App\Models\PaymentState;
use App\Models\Client;
use App\Models\Address;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\ShippingHistory;
use App\Models\Event;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    /**** Relationship ****/
    public function details() {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }

    public function billing() {
        return $this->hasOne(Billing::class, 'order_id', 'id');
    }

    public function client() {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function shipping() {
        return $this->belongsTo(ShippingState::class, 'shipping_state_id', 'id');
    }

    public function payment() {
        return $this->belongsTo(PaymentState::class, 'payment_state_id', 'id');
    }

    public function address() {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }

    public function histories() {
        return $this->hasMany(ShippingHistory::class, 'order_id', 'id');
    }

    public function province() {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }

    public function address_type() {
        return $this->belongsTo(AddressesType::class, 'addresses_type_id', 'id');
    }

    /**** Scopes ****/
    public function scopeWhereSearch($query, $search) {
    $query->where('total', 'LIKE', '%' . $search . '%')
          ->orWhere('reference_code', 'LIKE', '%' . $search . '%')
          ->orWhere('ip', 'LIKE', '%' . $search . '%');
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy) {
        $query->orderByRaw('(IFNULL('. $orderByField .', id)) '. $orderBy);
    }

    public function scopeApplyFilters($query, array $filters) {
        $filters = collect($filters);

        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }
        
        if ($filters->get('clientId')) {
            $query->where('client_id', $filters->get('clientId'));
        }

        if ($filters->get('wholesale') || $filters->get('wholesale') === '0') {
            $query->where('wholesale', $filters->get('wholesale'));
        }

        if ($filters->get('shipping_state_id')) {
            $query->where('shipping_state_id', $filters->get('shipping_state_id'));
        }

        if ($filters->get('payment_state_id')) {
            $query->where('payment_state_id', $filters->get('payment_state_id'));
        }

        if ($filters->get('type') || $filters->get('type') === '0') {
            $query->where('type', $filters->get('type'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'order_id';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }

        if(Auth::check() && Auth::user()->getRoleNames()[0] === 'Proveedor') {
            $query->whereHas('details.product_color.product', function ($q) {
                $q->where('user_id', Auth::user()->id);
            })->orWhereHas('details.service', function ($q) {
                $q->where('user_id', Auth::user()->id);
            });
        }
    }

    public function scopePaginateData($query, $limit) {
        if ($limit == 'all') {
            return collect(['data' => $query->get()]);
        }

        return $query->paginate($limit);
    }

    /**** Public methods ****/
    public static function createOrder($request) {

        $order = self::create([
            'client_id' => $request->client_id === null ? null : $request->client_id,
            'address_id' => $request->client_id === null ? null : $request->address_id,
            'coupon_id' => $request->coupon_id === 0 ? null : $request->coupon_id,
            'date' => now(),
            'sub_total' => $request->sub_total,
            'shipping_total' => $request->shipping_total,
            'shipping_express' => $request->shipping_express,
            'tax' => $request->tax,
            'total' => $request->total,
            'wholesale' => $request->wholesale,
            'type' => $request->type,
            'ip' => $request->ip === null ? null : $request->ip,
            'user_agent' => $request->user_agent === null ? null : $request->user_agent
        ]);

        $addressFind = collect($request->addresses)->firstWhere('id', $request->address_id);
        
        if ($addressFind && $request->client_id === null) {
            $order->update([
                'province_id' => $addressFind['province_id'],
                'addresses_type_id' => intval($addressFind['addresses_type_id']),
                'shipping_phone' => $addressFind['phone'],
                'shipping_address' => $addressFind['address'],
                'shipping_street' => $addressFind['street'],
                'shipping_city' => $addressFind['city'],
                'shipping_postal_code' => $addressFind['postal_code']
            ]);
        }
        
        switch (intval($request->type)) {
            case 0: //products
                $prefix = ($request->wholesale === 0 ? '03' : '05');
            break;
            case 1: //services
                $prefix = '09';
            break;
            case 2: //mixto
                $prefix = '01';
            break;
            default:
                $prefix = ($request->wholesale === 0 ? '03' : '05');
        }

        if($request->type === 0 )
            $reference_code = Order::withTrashed()
                           ->where([['wholesale', $request->wholesale],['type', 0]])
                           ->latest('reference_code')
                           ->first()
                           ->reference_code ?? $prefix.'0000000';
        else 
            $reference_code = Order::withTrashed()
                           ->where('type', $request->type)
                           ->latest('reference_code')
                           ->first()
                           ->reference_code ?? $prefix.'0000000';

        $order->update([
            'reference_code' => self::generateNextCode($reference_code)
        ]);

        //Order_details
        foreach ($request->product_color_id as $index => $productColorId) {
            $detail = OrderDetail::create([
                'order_id' => $order->id,
                'product_color_id' => $productColorId,
                'price' => $request->price_product[$index],
                'quantity' => $request->quantity_product[$index],
                'total' => $request->price_product[$index] * $request->quantity_product[$index],
            ]);
        }

        foreach ($request->service_id as $index => $serviceId) {
            $detail = OrderDetail::create([
                'order_id' => $order->id,
                'service_id' => $serviceId,
                'cake_size_id' => $request->cake_size_id[$index] === null ? null : $request->cake_size_id[$index],
                'flavor_id' => $request->flavor_id[$index] === null ? null : $request->flavor_id[$index],
                'filling_id' => $request->filling_id[$index] === null ? null : $request->filling_id[$index],
                'order_file_id' => $request->order_file_id[$index] === null ? null : $request->order_file_id[$index],
                'price' => $request->price_service[$index],
                'date' => $request->date[$index],
                'quantity' => $request->quantity_service[$index],
                'total' => $request->price_service[$index] * $request->quantity_service[$index],
            ]);


            $order_create = Order::with(['details.service.categories'])->find($order->id);

            foreach($order_create->details as $key => $detail) {
                if(!is_null($detail->service)) {
                    $event = new Event;
                    $event->category_id = $detail->service->categories[0]->category_id;
                    $event->order_detail_id = $detail->id;
                    $event->title = $order_create->reference_code . '-' .$detail->id;
                    $event->start_date = $detail->date;
                    $event->end_date = $detail->date;
                    $event->save();
                }
            }
        }

        //Billing
        $billing = Billing::create([
            'order_id' => $order->id,
            'province_id' => $request->province_id,
            'document_type_id' => $request->document_type_id,
            'document' => $request->document,
            'name' => $request->name,
            'last_name' => $request->last_name,
            'company' => $request->company,
            'email' => strtolower($request->email),
            'phone' => $request->phone,
            'address' => $request->address,
            'street' => $request->street,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'note' => $request->note
        ]);

        //history
        ShippingHistory::create([
            'order_id' => $order->id,
            'shipping_state_id' => 1
        ]);

        return $order;
    }

    public static function sendOrder($order, $request) {
        $order->update([
            'shipping_state_id' => $request->shipping_state_id
        ]);   

        // history
        ShippingHistory::create([
            'order_id' => $order->id,
            'shipping_state_id' => $request->shipping_state_id,
            'reason' => $request->reason
        ]);
        
        if($request->shipping_state_id === '3')
            self::sendMailEvaluation($order->id);
        if($request->shipping_state_id !== '1')
            self::sendMail($order->id, $request->shipping_state_id, $request->reason);
    
        return $order;
    }

    public static function deleteOrder($id) {
        self::deleteOrders(array($id));
    }

    public static function deleteOrders($ids) {
        foreach ($ids as $id) {
            $order = self::find($id);
            $order->delete();
        }
    }

    public static function updatePaymentState($request, $order) {
 
        $order->update([
            'payment_state_id' => $request->payment_state_id
        ]);
    
        return $order;
    }

    public static function updateInventary($order) {

        $orderDetails = 
            OrderDetail::with(['product_color'])
                       ->where('order_id', $order->id)
                       ->get(); 
        
        $productDetails = $orderDetails->map(function ($detail) {
            return [
                'product_id' => $detail->product_color ? $detail->product_color->product_id : 0,
                'quantity' => $detail->quantity,
                'total' => $detail->total
            ];
        })->toArray();

        foreach ($productDetails as $item) {
            $product = Product::with(['colors.product', 'user'])->find($item['product_id']);
            if ($product) {
                $product->updateStockProduct($product, $item['quantity']);  
                
                $supplier = Supplier::where('user_id', $product->user_id)->first();
                if ($supplier) 
                    $supplier->updateSales($item['total'], $supplier, $order);
            }
        }
    }

    public static function generateNextCode($lastCode) {
        $prefix = substr($lastCode, 0, 2); 
        $number = (int) substr($lastCode, 2);
        $number++;
        if ($number > 999999) {
            $prefix++;
            $number = 1000000;
        }
    
        return $prefix . sprintf('%06d', $number);
    }

    public static function sendMailEvaluation($orderId) {
        $order = 
            Order::with([
                'billing',
                'details.product_color.product', 
                'details.service',
                'client.user.userDetail'
            ])->find($orderId); 


        if($order->client) {
            $user = $order->client->user->name . ' ' . $order->client->user->last_name;
            $email = $order->client->user->email;
            $link = env('APP_DOMAIN').'/detail-purchases/'.$orderId;

            $text = '¡Gracias por tu compra, <strong>'.$user.'</strong>!<br>';
            $text .= 'Esperamos que los productos que elegiste hayan hecho de tu fiesta un momento inolvidable. Ahora, nos encantaría conocer tu experiencia. ';
            $text .= 'Tu opinión es clave para ayudarnos a mejorar y ofrecerte un mejor servicio. ';
            $text .= '<strong>Tómate un minuto y déjanos tu valoración</strong>. Además, al calificar, seguirás accediendo a promociones y beneficios exclusivos.';
    
            $text2 = '¡Gracias por ser parte de nuestra comunidad!<br>Saludos';
    
            $buttonText = 'Califica ahora';

        } else {
            $user = $order->billing->name . ' ' . $order->billing->last_name;
            $email = $order->billing->email;
            $link = env('APP_DOMAIN').'/register/form_client';

            $text = '¡Gracias por tu compra, <strong>'.$user.'</strong>!<br>';
            $text .= 'Los productos que elegiste están listos para hacer de tu fiesta un momento especial. ';
            $text .= 'Tu calificación nos ayuda a mejorar y brindarte un mejor servicio. ';
            $text .= 'Para dejar tu opinión, solo necesitas registrarte en nuestra plataforma y acceder a promociones exclusivas que pronto tendremos para ti.<br>';
            $text .= 'Registrarte ¡Es rápido y fácil!<br>';
            $text2 = '¡Esperamos tu valoración!<br>Saludos';

            $buttonText = 'Regístrate y califica aquí';
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
                    'service_image' => asset('storage/' . $detail->service->image),
                    'slug' =>env('APP_DOMAIN').'/services/'.$detail->service->slug,
                ];
                
                array_push($services, $serviceInfo);
            }
        }

        $subject = '¿cómo calificarías tus productos? ¡Nos encantaría saberlo!';

        $data = [
            'products' => $products,
            'services' => $services,
            'link' => $link,
            'title' => '¿Qué te pareció tu producto? ',
            'text' => $text,
            'text2' => $text2,
            'buttonText' => $buttonText
        ];

        try {
            \Mail::send(
                'emails.clients.send_evaluation'
                , ['data' => $data]
                , function ($message) use ($email, $subject) {
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    $message->to($email)->subject($subject);
            });
        } catch (\Exception $e){
            $message = 'error';
            $responseMail = $e->getMessage();

            Log::info($message . ' ' . $responseMail);
        } 

    }

    public static function sendMail($orderId, $shipping_state_id, $reason) {

        $order = 
            Order::with([
                'province',
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
                    'slug' => env('APP_DOMAIN').'/products/'.$detail->product_color->product->slug,
                    'quantity' => $detail->quantity,
                    'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
                ];
                
                array_push($products, $productInfo);
            }
        }

        switch ($shipping_state_id) {
            case '2':
                $title = 'Pedido no entregado';
                $text = 'No se ha podido enviar su pedido.';
                $subject = 'Pedido #'.$order->reference_code.' fuera de entrega.';
                break;
            case '3':
                $title = 'Llegó tu compra';
                $text = 'Hicimos entrega de tu producto en ';
                $subject = 'Tu pedido #'.$order->reference_code.' ha sido entregado.';
                break;
            case '4':
                $title = 'Pedido enviado';
                $text = 'Enviamos tu pedido a ';
                $subject = 'Tu pedido #'.$order->reference_code.' ha sido enviado.';
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

        try {
            \Mail::send(
                'emails.clients.send_orders'
                , ['data' => $data]
                , function ($message) use ($email, $subject) {
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    $message->to($email)->subject($subject);
            });
        } catch (\Exception $e){
            $message = 'error';
            $responseMail = $e->getMessage();

            Log::info($message . ' ' . $responseMail);
        } 
    }
}
