<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Filling extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Scopes ****/
    public function scopeWhereSearch($query, $search) {
        $query->where('name', 'LIKE', '%' . $search . '%');
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
 
    /**** Public methods ****/
    public static function createFilling($request) {
        $filling = self::create([
            'name' => $request->name
        ]);
 
        return $filling;
    }
 
    public static function updateFilling($request, $filling) {
        $filling->update([
            'name' => $request->name
        ]);
 
        return $filling;
     }
 
    public static function deleteFilling($id) {
        self::deleteFillings(array($id));
    }
 
    public static function deleteFillings($ids) {
        foreach ($ids as $id) {
            $filling = self::find($id);
            $filling->delete();
        }
    }
}
