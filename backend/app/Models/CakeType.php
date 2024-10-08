<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CakeType extends Model
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
    public static function createCakeType($request) {
        $cakeType = self::create([
            'name' => $request->name
        ]);
 
        return $cakeType;
    }
 
    public static function updateCakeType($request, $cakeType) {
        $cakeType->update([
            'name' => $request->name
        ]);
 
        return $cakeType;
     }
 
    public static function deleteCakeType($id) {
        self::deleteCakeTypes(array($id));
    }
 
    public static function deleteCakeTypes($ids) {
        foreach ($ids as $id) {
            $cakeType = self::find($id);
            $cakeType->delete();
        }
    }
}
