<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

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
        $order = self::create([
            
        ]);

        return $order;
    }

    public static function updateOrder($request, $order) {
        $order->update([
          
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
}