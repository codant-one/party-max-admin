<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ClientIp extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    /**** Relationship ****/
    public function client() {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function registrations() {
        return $this->hasMany(ClientRegistration::class, 'ip_id', 'id');
    }

    /**** Scopes ****/
    public function scopeWhereSearch($query, $search) {
        $query->where('ip', 'LIKE', '%' . $search . '%')
              ->orWhere('device', 'LIKE', '%' . $search . '%')
              ->orWhere('plataform', 'LIKE', '%' . $search . '%')
              ->orWhere('browser', 'LIKE', '%' . $search . '%');
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
    public static function updateStatesIp($request, $ip) {

        $ip->update([
            'is_blocked' => $request->is_blocked
        ]);  

        return $ip;
    }

    public static function deleteIp($id) {
        self::deleteIps(array($id));
    }

    public static function deleteIps($ids) {
        foreach ($ids as $id) {
            $ip = self::find($id);
            $ip->delete();
        }
    }
}
