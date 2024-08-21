<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\BrandType;

class Brand extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
     public function tag_type() {
        return $this->belongsTo(BrandType::class, 'brand_type_id', 'id');
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
    public static function createBrand($request) {
        $brand = self::create([
            'brand_type_id' => $request->brand_type_id,
            'name' => $request->name
        ]);

        return $brand;
    }

    public static function updateBrand($request, $brand) {
        $brand->update([
            'brand_type_id' => $request->brand_type_id,
            'name' => $request->name
        ]);

        return $brand;
    }

    public static function deleteBrand($id) {
        self::deleteBrands(array($id));
    }

    public static function deleteBrands($ids) {
        foreach ($ids as $id) {
            $brand = self::find($id);
            $brand->delete();
        }
    }
}
