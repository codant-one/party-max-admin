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

    /**** Scopes ****/
    public function scopeSales($query)
    {
        return  $query->addSelect(['sales' => function ($q){
                    $q->selectRaw('SUM(CAST(o.total AS DECIMAL(10, 2)))')
                        ->from('clients as c')
                        ->leftJoin('orders as o', 'o.client_id', '=', 'c.id')
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
                'address.province', 
                'client.user.userDetail'
            ])->find($orderId); 

        $link_send = env('APP_DOMAIN').'/detail-purchases/'.$orderId;
        $link_purchases = env('APP_DOMAIN').'/purchases';
        $note = is_null($order->billing->note) ? '.' : '. (' . $order->billing->note . ').';

        $address = 
            $order->address->address . ', ' . 
            $order->address->street . ', ' . 
            $order->address->city . ', ' . 
            $order->address->postal_code . ', ' . 
            $order->address->province->name .
            $note;

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
                'slug' => $detail->product_color->product->slug,
                'quantity' => $detail->quantity,
                'text_quantity' => ($detail->quantity === '1') ? 'Unidad' : 'Unidades'
            ];
            
            array_push($products, $productInfo);
        
        }

        $data = [
            'address' => $address,
            'user' => $order->client->user->name . ' ' . $order->client->user->last_name,
            'phone' => $order->client->user->userDetail->phone,
            'total' => $order->total,
            'payment_method' => $payment_method,
            'products' => $products,
            'link_send' => $link_send,
            'link_purchases' => $link_purchases
        ];
        
        $email = $order->client->user->email;
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
}
