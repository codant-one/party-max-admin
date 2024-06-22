<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

use App\Models\OrderDetail;
use App\Models\ShippingState; 
use App\Models\PaymentState;
use App\Models\Client;
use App\Models\Address;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\ShippingHistory;

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

    /**** Scopes ****/
    public function scopeWhereSearch($query, $search) {
    $query->where('total', 'LIKE', '%' . $search . '%')
          ->orWhere('reference_code', 'LIKE', '%' . $search . '%');
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

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'order_id';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
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
            'client_id' => $request->client_id,
            'address_id' => $request->address_id,
            'date' => now(),
            'sub_total' => $request->sub_total,
            'shipping_total' => $request->shipping_total,
            'shipping_express' => $request->shipping_express,
            'tax' => $request->tax,
            'total' => $request->total,
            'wholesale' => $request->wholesale 
        ]);

        $prefix = $request->wholesale === 0 ? '06-' : '09-';

        $reference_code = Order::where('wholesale', $request->wholesale)
                           ->latest('reference_code')
                           ->first()
                           ->reference_code ?? $prefix.rand(1,999999999);
                        //    reference_code ?? $prefix.'00000001'
                        // ->reference_code ?? $prefix.rand(1,999999999)   ;
        $order->update([
            'reference_code' => self::generateNextCode($reference_code)
        ]);

        //Order_details
        foreach ($request->product_color_id as $index => $productColorId) {
            $detail = OrderDetail::create([
                'order_id' => $order->id,
                'product_color_id' => $productColorId,
                'price' => $request->price[$index],
                'quantity' => $request->quantity[$index],
                'total' => $request->price[$index] * $request->quantity[$index],
            ]);
        }

        //Billing
        $billing = Billing::create([
            'order_id' => $order->id,
            'province_id' => $request->province_id,
            'name' => $request->name,
            'last_name' => $request->last_name,
            'company' => $request->company,
            'email' => $request->email,
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

        // history
        ShippingHistory::create([
            'order_id' => $order->id,
            'shipping_state_id' => $request->shipping_state_id
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
                'product_id' => $detail->product_color->product_id,
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
        if ($number > 9999999) {
            $prefix++;
            $number = 10000000;
        }
    
        return $prefix . sprintf('%06d', $number);
    }

    public static function sendMail($orderId, $shipping_state_id, $reason) {

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

        $address = 
            $order->address->address . ', ' . 
            $order->address->street . ', ' . 
            $order->address->city . ', ' . 
            $order->address->postal_code . ', ' . 
            $order->address->province->name .
            $note;

        $products = [];

        foreach ($order->details as $detail) {
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
            'user' => $order->client->user->name . ' ' . $order->client->user->last_name,
            'products' => $products,
            'link' => $link,
            'link_contact' => $link_contact,
            'title' => $title,
            'text' => $text,
            'reason' => $reason,
            'shipping_state_id' => $shipping_state_id
        ];
        
        $email = $order->client->user->email;

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
