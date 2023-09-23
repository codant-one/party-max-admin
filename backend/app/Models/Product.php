<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function categories()
    {
        return $this->hasMany(ProductCategory::class, 'product_id');
    }

    public function detail()
    {
        return $this->hasOne(ProductDetail::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    /**** Scopes ****/
    public function scopeWhereSearch($query, $search) {
        $query->where('name', 'LIKE', '%' . $search . '%')
              ->orWhere('description', 'LIKE', '%' . $search . '%')
              ->orWhere('sku', 'LIKE', '%' . $search . '%');
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
    public static function createCategory($request) {
         
        $slug = self::getSlug($request);
 
        $category = self::create([
            'category_id' => ($request->is_category) ? $request->category_id : null,
            'name' => $request->name,
            'description' => $request->description === 'null' ? null : $request->description,
            'slug' => $slug
        ]);
 
        return $category;
    }
 
    public static function updateCategory($request, $category) {
        $slug = self::getSlug($request);
 
        $category->update([
            'category_id' => ($request->is_category) ? $request->category_id : null,
            'name' => $request->name,
            'description' => $request->description === 'null' ? null : $request->description,
            'slug' => $slug
        ]);
 
        return $category;
    }
 
    public static function deleteCategories($ids) {
        foreach ($ids as $id) {
            $category = self::with(['children.children'])->find($id);
            $category->delete();
 
        }
    }
}
