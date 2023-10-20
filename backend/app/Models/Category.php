<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
class Category extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function category() {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function product()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'category_id');
    }

    public function recursiveItems()
    {
        return $this->children()->with('recursiveItems');
    }

    public static function getRecursiveItems($category_id = null)
    {
        return self::with('recursiveItems')->where('category_id', $category_id)->get();
    }


    /**** Scopes ****/
    public function scopeWhereSearch($query, $search) {
       $query->where('name', 'LIKE', '%' . $search . '%')
             ->orWhere('slug', 'LIKE', '%' . $search . '%');
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

    public static function getSlug($request) {

        $categories = self::get()->toArray();

        $grandfather = '';
        $father = '';

        if($request->is_category) {
            $result = [];
            $category_id = intval($request->category_id);
            
            $result = array_filter($categories, function ($element) use ($category_id) {
                return $element['id'] === $category_id;
            });

            $result = array_values($result)[0];

            if(!is_null($result['category_id'])) {
                $result2 = [];
                $category_id = $result['category_id'];
                $result2 = array_filter($categories, function ($element) use ($category_id) {
                    return $element['id'] === $category_id;
                });

                // Convertir el resultado nuevamente en un array indexado
                $result2 = array_values($result2)[0];

                $grandfather = Str::slug($result2['name']) . '/';
                $father = Str::slug($result['name']) . '/';
            } else {
                $father = Str::slug($result['name']) . '/';
            }
        }

        return $grandfather . $father . Str::slug($request->name);
    }
    
    /**** Public methods ****/
    public static function createCategory($request) {
        
        $slug = self::getSlug($request);

        $category = self::create([
            'category_id' => ($request->is_category) ? $request->category_id : null,
            'name' => $request->name,
            'slug' => $slug
        ]);

        return $category;
    }

    public static function updateCategory($request, $category) {
        $slug = self::getSlug($request);

        $category->update([
            'category_id' => ($request->is_category) ? $request->category_id : null,
            'name' => $request->name,
            'slug' => $slug
        ]);

        return $category;
    }

    public static function deleteCategories($ids) {
        foreach ($ids as $id) {
            $category = self::with(['children.children'])->find($id);
            $category->delete();

            if(count($category->children) > 0) {
                deleteFile($category->children[0]->banner);
                deleteFile($category->children[0]->banner_2);
                deleteFile($category->children[0]->banner_3);
                deleteFile($category->children[0]->banner_4);
                if(count($category->children[0]->children) > 0) {
                    deleteFile($category->children[0]->children[0]->banner);
                    deleteFile($category->children[0]->children[0]->banner_2);
                    deleteFile($category->children[0]->children[0]->banner_3);
                    deleteFile($category->children[0]->children[0]->banner_4);
                }
            }

            if($category->banner) {
                deleteFile($category->banner);
                deleteFile($category->banner_2);
                deleteFile($category->banner_3);
                deleteFile($category->banner_4);
            }
        }
    }
}
