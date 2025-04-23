<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Models\Address;
use App\Models\Order;

class Client extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    /**** Relationship ****/
    public function state() {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    public function gender() {
        return $this->belongsTo(Gender::class, 'gender_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function addresses() {
        return $this->hasMany(Address::class, 'client_id');
    }

    public function orders() {
        return $this->hasMany(Order::class, 'client_id');
    }

    public function coupons() {
        return $this->hasMany(Coupon::class, 'client_id');
    }

    public function registrations(){
        return $this->hasMany(ClientRegistration::class, 'client_id', 'id');
    }

    /**** Scopes ****/
    public function scopeSales($query)
    {
        return  $query->addSelect(['sales' => function ($q){
                    $q->selectRaw('SUM(CAST(o.sub_total AS DECIMAL(10, 2)))')
                        ->from('clients as c')
                        ->leftJoin('orders as o', 'o.client_id', '=', 'c.id')
                        ->where('o.payment_state_id', 4)
                        ->whereColumn('c.id', 'clients.id')
                        ->groupBy('c.id');
                }]);
    }

    public function scopeWhereSearch($query, $search) {
        $query->whereHas('user', function ($q) use ($search) {
            $q->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%')
                      ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('username', 'LIKE', '%' . $search . '%')
                      ->orWhere('email', 'LIKE', '%' . $search . '%');
            });
        });
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy) {
        $query->orderByRaw('(IFNULL('. $orderByField .', id)) '. $orderBy);
    }

    public function scopeApplyFilters($query, array $filters) {
        $filters = collect($filters);

        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
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
    public static function createClient($request) {
        $user = User::createUser($request);
        $user->assignRole('Cliente');

        $client = self::create([
            'user_id' => $user->id,
            'gender_id' => $request->gender_id,
            'birthday' => date('Y-m-d', strtotime($request->birthday) )
        ]);
        
        return $client;
    }

    public static function updateClient($request, $client) {
        $client->update([
            'gender_id' => $request->gender_id,
            'birthday' => date('Y-m-d', strtotime($request->birthday) )
        ]);

        $user = User::find($client->user_id);
        $request->merge([ 'email' => $user->email ]);
        User::updateUser($request, $user);

        return $client;
    }

    public static function deleteClient($id) {
        self::deleteClients(array($id));
    }

    public static function deleteClients($ids) {
        foreach ($ids as $id) {
            $client = self::find($id);
            $user = User::find($client->user_id);

            $client->delete();
            User::deleteUser($user->id);
        }
    }

    public static function updateOrCreateClientProfile($request, $user) {

        $clientD = Client::updateOrCreate(
            [    'user_id' => $user->id ],
            [
                'gender_id' => $request->gender_id,
                'birthday' => date('Y-m-d', strtotime($request->birthday) )
            ]
        );

        return $clientD;
    }

    public static function sendMail($orderId) {

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
                    'service_image' => asset('storage/' . $detail->service->image),
                    'flavor' => $detail->flavor ? $detail->flavor->name : null,
                    'filling' => $detail->filling ? $detail->filling->name : null,
                    'cake_size' => $detail->cake_size ? $detail->cake_size->name : null,
                    'slug' =>env('APP_DOMAIN').'/services/'.$detail->service->slug,
                    'quantity' => $detail->quantity,
                    'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
                ];
                
                array_push($services, $serviceInfo);
            }
        
        }

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
        
        $subject = 'Tu pedido en PARTYMAX se completó con éxito';

        try {
            \Mail::send(
                'emails.payment.purchase_detail'
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

    public static function sendInfo($orderId) {

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
                    'service_image' => asset('storage/' . $detail->service->image),
                    'flavor' => $detail->flavor ? $detail->flavor->name : null,
                    'filling' => $detail->filling ? $detail->filling->name : null,
                    'cake_size' => $detail->cake_size ? $detail->cake_size->name : null,
                    'slug' =>env('APP_DOMAIN').'/services/'.$detail->service->slug,
                    'quantity' => $detail->quantity,
                    'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
                ];
                
                array_push($services, $serviceInfo);
            }
        
        }

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
        
        $email = env('MAIL_TO_INFO');
        $subject = 'Tienes un nuevo pedido.';

        try {
            \Mail::send(
                'emails.payment.info_order'
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

    public static function sendMailError($orderId, $payment_state_id, $message) {

        $order =  Order::with(['client.user.userDetail'])->find($orderId); 

        if($order->client) {
            $user = $order->client->user->name . ' ' . $order->client->user->last_name;
            $email = $order->client->user->email;
        } else {
            $user = $order->billing->name . ' ' . $order->billing->last_name;
            $email = $order->billing->email;
        }

        $data = [
            'orderId' => $order->id,
            'user' => $user,
            'message' => $message,
            'payment_state_id' => $payment_state_id === 3 ? 'FALLIDA' : 'CANCELADA'
        ];
        
        $subject = 'Tu pedido en PARTYMAX no se ha podido completar con éxito';

        try {
            \Mail::send(
                'emails.payment.failed'
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
