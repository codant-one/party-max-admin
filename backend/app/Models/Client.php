<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    public function state(){
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    public function gender(){
        return $this->belongsTo(Gender::class, 'gender_id', 'id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    /**** Scopes ****/
    public function scopeWhereSearch($query, $search) {
       $query->where('title', 'LIKE', '%' . $search . '%')
             ->orWhere('description', 'LIKE', '%' . $search . '%');
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
        $client = self::create([
            'user_id' => $request->user_id,
            'gender_id' => $request->gender_id,
            'birthcountry_id' => $request->birthcountry_id,
            'nationality_id' => $request->nationality_id,
            'birthday' => $request->birthday
        ]);

        return $client;
    }

    public static function updateClient($request, $client) {
        $client->update([
            // 'user_id' => $request->user_id,
            'gender_id' => $request->gender_id,
            'birthcountry_id' => $request->birthcountry_id,
            'nationality_id' => $request->nationality_id,
            'birthday' => $request->birthday
        ]);

        return $client;
    }

    public static function deleteClient($id) {
        self::deleteClients(array($id));
    }

    public static function deleteClients($ids) {
        foreach ($ids as $id) {
            $client = self::find($id);
            $client->delete();
        }
    }
}
