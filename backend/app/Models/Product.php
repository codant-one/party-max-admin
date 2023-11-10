<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\ProductCategory;
use App\Models\ProductDetail;
use App\Models\ProductImage;
use App\Models\User;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function categories()
    {
        return $this->hasMany(ProductCategory::class, 'product_image_id');
    }

    public function detail()
    {
        return $this->hasOne(ProductDetail::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function productlike()
    {
        return $this->hasMany(ProductLike::class, 'product_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    /**** Scopes ****/
    public function scopeFavorites($query)
    {
        return  $query->addSelect(['likes' => function ($q){
                    $q->selectRaw('COUNT(p.id)')
                      ->from('products as p')
                      ->join('product_likes as p_l', 'p.id', '=', 'p_l.product_id')
                      ->whereColumn('p.id', 'products.id');
                }]);
    }

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

    public static function updateProductCategories($product_id, $request) {
        ProductCategory::where('product_id', $product_id)->delete();

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

    public static function updateProductDetails($product_id, $request) {
        $product_details = ProductDetail::where('product_id', $product_id)->first();
        $product_details->width = $request->width;
        $product_details->height = $request->height;
        $product_details->deep = $request->deep;
        $product_details->weigth = $request->weigth;
        $product_details->save();
    }

    public static function createProductImages($product_id, $request) {
        foreach(json_decode($request->colors, true) as $color) {
            if ($request->hasFile('color_'.$color['color_id'])) {
                $images = $request->file('color_'.$color['color_id']);
        
                foreach ($images as $image) {

                    $path = 'products/gallery/';
        
                    $file_data = uploadFile($image, $path);

                    $product_image = ProductImage::create([
                        'product_id' => $product_id,
                        'color_id' => $color['color_id'],
                        'image' => $file_data['filePath']
                    ]);
                }
            }
        }
    }

    public static function updateProductImages($product_id, $request) {

        if($request->update){
            $products_images = ProductImage::where('product_id', $product_id)->get();

            foreach($products_images as $products_image){
                ProductImage::where('product_id', $product_id)->delete();
                
                if($products_image->image)
                    deleteFile($products_image->image);
            }
        }

        foreach(json_decode($request->colors, true) as $color) {
            if ($request->hasFile('color_'.$color['color_id'])) {
                $images = $request->file('color_'.$color['color_id']);
            
                foreach ($images as $image) {

                    $path = 'products/gallery/';
        
                    $file_data = uploadFile($image, $path);

                    $product_image = ProductImage::create([
                        'product_id' => $product_id,
                        'color_id' => $color['color_id'],
                        'image' => $file_data['filePath']
                    ]);
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
 
        $product->update([
            'name' => $request->name,
            'description' => $request->description === 'null' ? null : $request->description,
            'sku' => $request->sku,
            'price' => $request->price,
            'price_for_sale' => $request->price_for_sale,
            'stock' => $request->stock,
            'slug' => Str::slug($request->name)
        ]);

        self::updateProductCategories($product->id, $request);
        self::updateProductDetails($product->id, $request);
        self::updateProductImages($product->id, $request);
 
        return $product;
    }
 
    public static function deleteProducts($ids) {
        foreach ($ids as $id) {
            $categproductory = self::with(['children.children'])->find($id);
            $product->delete();
        }
    }
}
