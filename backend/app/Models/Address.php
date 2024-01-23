<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\AddressesType;

class Address extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function type() {
        return $this->belongsTo(AddressesType::class, 'addresses_type_id', 'id');
    }

    /**** Public methods ****/
    public static function createAddress($request) {

        $address = self::create([
            'client_id' => $request->client_id,
            'addresses_type_id' => $request->addresses_type_id,
            'province_id' => $request->province_id,
            'title' => $request->title,
            'phone' => $request->phone,
            'address' => $request->address,
            'street' => $request->street,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'default' => $request->default
        ]);

        return $address;
    }

    public static function updateAddress($request, $address) {

        $address->update([
            'client_id' => $request->client_id,
            'addresses_type_id' => $request->addresses_type_id,
            'province_id' => $request->province_id,
            'title' => $request->title,
            'phone' => $request->phone,
            'address' => $request->address,
            'street' => $request->street,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'default' => $request->default
        ]);

        return $address;
    }

    public static function deleteAddress($id) {
        $address = self::find($id);
        $address->delete();
    }

    /**** Scopes ****/
    public function scopeWhereSearch($query, $search) {
        $query->where('title', 'LIKE', '%' . $search . '%')
              ->orWhere('phone', 'LIKE', '%' . $search . '%')
              ->orWhere('address', 'LIKE', '%' . $search . '%')
              ->orWhere('street', 'LIKE', '%' . $search . '%')
              ->orWhere('city', 'LIKE', '%' . $search . '%')
              ->orWhere('postal_code', 'LIKE', '%' . $search . '%');
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy) {
        $query->orderBy($orderByField, $orderBy);
    }
    
    public function scopeApplyFilters($query, array $filters) {
        $filters = collect($filters);

        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'created_at';
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
}
