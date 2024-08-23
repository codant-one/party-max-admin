<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\CakeType;

class CakeSize extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function cake_type() {
        return $this->belongsTo(CakeType::class, 'cake_type_id', 'id');
    }

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

        if ($filters->get('cake_type_id')) {
            $query->where('cake_type_id', $filters->get('cake_type_id'));
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
    public static function createCakeSize($request) {
        $cakeSize = self::create([
            'cake_type_id' => $request->cake_type_id,
            'name' => $request->name
        ]);
 
        return $cakeSize;
    }
 
    public static function updateCakeSize($request, $cakeSize) {
        $cakeSize->update([
            'cake_type_id' => $request->cake_type_id,
            'name' => $request->name
        ]);
 
        return $cakeSize;
    }
 
    public static function deleteCakeSize($id) {
        self::deleteCakeSizes(array($id));
    }
 
    public static function deleteCakeSizes($ids) {
        foreach ($ids as $id) {
            $cakeSize = self::find($id);
            $cakeSize->delete();
        }
    }
}
