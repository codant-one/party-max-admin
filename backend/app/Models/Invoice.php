<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    /**** Relationship ****/
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id','id');
    }

    public function orders()
    {
        return $this->hasMany(OrderDetail::class, 'invoice_id','id');
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
        

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'order_id';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }

        if(Auth::check() && Auth::user()->getRoleNames()[0] === 'Proveedor') {
            $query->whereHas('orders.product_color.product', function ($q) {
                $q->where('user_id', Auth::user()->id);
            })->orWhereHas('orders.service', function ($q) {
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
    public static function createInvoice($request) {

        $invoice = self::create([
        ]);


        return $invoice;
    }


    public static function deleteInvoice($id) {
        self::deleteInvoices(array($id));
    }

    public static function deleteInvoices($ids) {
        foreach ($ids as $id) {
            $invoice = self::find($id);
            $invoice->delete();
        }
    }
}
