<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

use App\Models\Order;

class Coupon extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function client() {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function order() {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**** Scopes ****/
    public function scopeWhereSearch($query, $search) {
        $query->where('code', 'LIKE', '%' . $search . '%');
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

    public static function updateState($order) {

        $order = Order::find($order->id); 
        
        if(!is_null($order->coupon_id)) {
            $coupon = self::find($order->coupon_id);
            $coupon->order_id = $order->id;
            $coupon->is_used = 1;
            $coupon->purchase_date = now();
            $coupon->save();
        }

    }

}