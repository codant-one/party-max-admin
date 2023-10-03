<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\ProductCategory;
use App\Models\ProductDetail;
use App\Models\ProductImage;

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
    public static function createProductCategories($product_id, $request) {
        foreach(explode(",", $request->category_id) as $category_id){
            ProductCategory::create([
                'product_id' => $product_id,
                'category_id' => $category_id
            ]);
        }
    }

    public static function createProductDetails($product_id, $request) {
        ProductDetail::create([
            'product_id' => $product_id,
            'width' => $request->width,
            'height' => $request->height,
            'deep' => $request->deep,
            'weigth' => $request->weigth
        ]);
    }

    public static function createProductImages($product_id, $request) {
        foreach(json_decode($request->colors, true) as $color) {
            if ($request->hasFile('color_'.$color['color_id'])) {
                $images = $request->file('color_'.$color['color_id']);
        
                foreach ($images as $image) {

                    $product_image = ProductImage::create([
                        'product_id' => $product_id,
                        'color_id' => $color['color_id'],
                    ]);

                    $path = 'products/gallery/';
        
                    $file_data = uploadFile($image, $path);
        
                    $product_image->image = $file_data['filePath'];
                    $product_image->update();
                }
            }
        }
    }

    public static function createProduct($request) {
 
        $product = self::create([
            'name' => $request->name,
            'description' => $request->description === 'null' ? null : $request->description,
            'sku' => $request->sku,
            'price' => $request->price,
            'price_for_sale' => $request->price_for_sale,
            'stock' => $request->stock,
            'slug' => Str::slug($request->name)
        ]);

        self::createProductCategories($product->id, $request);
        self::createProductDetails($product->id, $request);
        self::createProductImages($product->id, $request);

        return $product;
    }
 
    public static function updateProduct($request, $product) {
        $slug = self::getSlug($request);
 
        $product->update([
            'category_id' => ($request->is_category) ? $request->category_id : null,
            'name' => $request->name,
            'description' => $request->description === 'null' ? null : $request->description,
            'slug' => $slug
        ]);
 
        return $product;
    }
 
    public static function deleteProducts($ids) {
        foreach ($ids as $id) {
            $categproductory = self::with(['children.children'])->find($id);
            $product->delete();
        }
    }
}
