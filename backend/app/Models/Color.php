<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Color extends Model
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
    public static function createColor($request) {
        $color = self::create([
            'name' => $request->name,
            'color' => $request->color,
            'is_gradient' => $request->is_gradient
        ]);
 
        return $color;
    }

    public static function updateColor($request, $color) {
        $color->update([
            'name' => $request->name,
            'color' => $request->color,
            'is_gradient' => $request->is_gradient
        ]);
 
        return $color;
    }
 
    public static function deleteColor($id) {
        self::deleteColors(array($id));
    }
 
    public static function deleteColors($ids) {
        foreach ($ids as $id) {
            $color = self::find($id);
            $color->delete();
        }
    }
}
