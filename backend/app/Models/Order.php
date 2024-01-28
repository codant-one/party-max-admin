<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\OrderDetail;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    /**** Relationship ****/
    public function details() {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }

    public function billing() {
        return $this->hasMany(Billing::class, 'order_id', 'id');
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
    public static function createOrder($request) {

        $total = $request->sub_total + $request->shipping_total + $request->tax;
        $order = self::create([
            'client_id' => $request->client_id,
            'shipping_state_id' => $request->shipping_state_id,
            'payment_state_id' => $request->payment_state_id,
            'address_id' => $request->address_id,
            'date' => $request->date,
            'sub_total' => $request->sub_total,
            'shipping_total' => $request->shipping_total,
            'tax' => $request->tax,
            'total' => $total, 
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
            'default' => $request->default,
            'pse' => $request->pse,
            'card_number' => $request->card_number,
            'name' => $request->name,
            'expired_date' => $request->expired_date,
            'cvv_code' => $request->cvv_code,
            'phone' => $request->phone,
            'address' => $request->address,
            'street' => $request->street,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
        ]);

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
            'payment_state_id' => $request->payment_state_id,
        ]);      
    
        return $order;
    }
}
