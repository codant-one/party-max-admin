<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Product;
use App\Models\CategoryType;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function category_type() {
        return $this->belongsTo(CategoryType::class, 'category_type_id', 'id');
    }

    public function category() {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function state() {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    public function banner1() {
        return $this->belongsTo(Category::class, 'banner_category_id', 'id');
    }
    
    public function banner2() {
        return $this->belongsTo(Category::class, 'banner2_category_id', 'id');
    }

    public function banner3() {
        return $this->belongsTo(Category::class, 'banner3_category_id', 'id');
    }

    public function banner4() {
        return $this->belongsTo(Category::class, 'banner4_category_id', 'id');
    }

    public function product() {
        return $this->hasMany(ProductCategory::class, 'category_id', 'id');
    }

    public function service() {
        return $this->hasMany(ServiceCategory::class, 'category_id', 'id');
    }

    public function children() {
        return $this->hasMany(Category::class, 'category_id');
    }

    public function recursiveItems() {
        return $this->children()->with('recursiveItems');
    }

    public static function getRecursiveItems($category_id = null) {
        return self::with('recursiveItems')->where('category_id', $category_id)->get();
    }

    public function productColors() {
        return $this->belongsToMany(ProductColor::class, 'product_categories', 'category_id', 'product_color_id')
                    ->orderBy('product_colors.created_at', 'desc');
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

        if ($filters->get('fathers') === '1') {
            $query->whereNull('category_id');
        }

        if ($filters->get('category_type_id')) {
            $query->where('category_type_id', $filters->get('category_type_id'));
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
    
    public function scopeCategoryTotalPrice($query)
    {
        return $query->addSelect(['sum' => function ($q){
                     $q->selectRaw('SUM(CAST(p.price_for_sale AS DECIMAL(10, 2)))')
                     ->from('categories as c')
                     ->leftJoin('product_categories as pc', 'c.id', '=', 'pc.category_id')
                     ->leftJoin('product_colors as pco', 'pco.id', '=', 'pc.product_color_id')
                     ->leftJoin('products as p', 'pco.product_id', '=', 'p.id')
                     ->whereColumn('c.id', 'categories.id')
                     ->groupBy('c.id');
        }]);
    }

    /**** Public methods ****/
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

    public static function createCategory($request) {
        
        $slug = self::getSlug($request);

        $category = self::create([
            'category_type_id' => $request->category_type_id,
            'category_id' => ($request->is_category) ? $request->category_id : null,
            'name' => $request->name,
            'slug' => $slug,
            'keywords' => $request->keywords === 'null' ? null : $request->keywords
        ]);

        return $category;
    }

    public static function updateCategory($request, $category) {
        $slug = self::getSlug($request);

        $category->update([
            'category_type_id' => $request->category_type_id,
            'category_id' => ($request->is_category) ? $request->category_id : null,
            'name' => $request->name,
            'slug' => $slug,
            'keywords' => $request->keywords === 'null' ? null : $request->keywords
        ]);

        if(!($request->is_category)){
            $subcategories = self::where('category_id', $category->id)->get();

            foreach($subcategories as $subcategory) {
                $request->request->remove('name');
                $request->request->add(['is_category' =>  1]);
                $request->request->add(['category_id' => intval($category->id)]);
                $request->request->add(['name' => $subcategory->name]);
                $slug_ = self::getSlug($request);

                $category_ = self::find($subcategory->id);
                $category_->slug = $slug_;
                $category_->update();

                $children = self::where('category_id', $subcategory->id)->get();

                foreach($children as $c) {
                    $request->request->remove('name');
                    $request->request->remove('category_id');
                    $request->request->add(['category_id' => intval($subcategory->id)]);
                    $request->request->add(['name' => $c->name]);
                    $slug_ = self::getSlug($request);
    
                    $category_ = self::find($c->id);
                    $category_->slug = $slug_;
                    $category_->update();
                }
            }
        }

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

    public static function updateStatesCategory($request, $category) {

        $category->update([
            'state_id' => $request->state_id
        ]);  

        return $category;
    }
}
