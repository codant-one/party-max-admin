<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
