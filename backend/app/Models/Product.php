<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Models\ProductCategory;
use App\Models\ProductDetail;
use App\Models\ProductImage;
use App\Models\ProductColor;
use App\Models\ProductTag;
use App\Models\User;
use App\Models\Brand;
use App\Models\State;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    /**** Relationship ****/
    public function detail()
    {
        return $this->hasOne(ProductDetail::class, 'product_id');
    }

    public function colors()
    {
        return $this->hasMany(ProductColor::class, 'product_id','id');
    }

    public function tags()
    {
        return $this->hasMany(ProductTag::class, 'product_id');
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

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id','id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id','id');
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
              ->orWhere('description', 'LIKE', '%' . $search . '%');
    }

    public function scopeWhereCategory($query, $search) {
        $query->whereHas('colors.categories', function ($q) use ($search) {
            $q->where('category_id', $search);
        });
    }

    public function scopeWhereCategorySlug($query, $search) {
        $query->whereHas('colors.categories.category', function ($q) use ($search) {
            $q->where('slug', $search);
        });
    }

    public function scopeWhereSubCategory($query, $search) {
        $query->whereHas('colors.categories.category', function ($q) use ($search) {
            $q->where('slug', 'LIKE', '%' . $search);
        });
    }

    public function scopeWhereColor($query, $search) {
        $query->whereHas('colors', function ($q) use ($search) {
            $q->where('color_id', $search);
        });
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy) {
        $query->orderByRaw('(IFNULL('. $orderByField .', id)) '. $orderBy);
    }
 
    public function scopeApplyFilters($query, array $filters) {
        $filters = collect($filters);

        if(Auth::user()->getRoleNames()[0] === 'Proveedor') {
            $query->where('user_id', Auth::user()->id);
        }
 
        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }

        if ($filters->get('favourite') !== null) {
            $query->where('favourite', $filters->get('favourite'));
        }

        if ($filters->get('archived') !== null) {
            $query->where('archived', $filters->get('archived'));
        }
        
        if ($filters->get('discarded') !== null) {
            $query->where('discarded', $filters->get('discarded'));
        }

        if ($filters->get('state_id') !== null) {
            $query->where('state_id', $filters->get('state_id'));
        }

        if ($filters->get('in_stock') !== null) {
            $query->where('in_stock', $filters->get('in_stock'));
        }

        if ($filters->get('category_id') !== null) {
            $query->whereCategory($filters->get('category_id'));
        }

        if ($filters->get('category') !== null) {
            $query->whereCategorySlug($filters->get('category'));
        }

        if ($filters->get('subcategory') !== null) {
            $query->whereSubCategory($filters->get('subcategory'));
        }

        if($filters->get('colorId') !== null){
            $query->whereColor($filters->get('colorId'));
        }

        if($filters->get('min')!=null && $filters->get('max')!=null) {
            $query->whereBetween(\DB::raw('CAST(wholesale_price AS DECIMAL(10,2))'),[$filters->get('min'), $filters->get('max')]);
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
    public static function createProductCategories($product_color_id, $key, $request) {
        $categories = json_decode($request->category_id, true);

        foreach($categories[$key] as $category_id) {
            ProductCategory::create([
                'product_color_id' => $product_color_id,
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
            'weigth' => $request->weigth,
            'material' => $request->material
        ]);
    }

    public static function updateProductDetails($product_id, $request) {
        $product_details = ProductDetail::where('product_id', $product_id)->first();
        $product_details->width = $request->width;
        $product_details->height = $request->height;
        $product_details->deep = $request->deep;
        $product_details->weigth = $request->weigth;
        $product_details->material = $request->material;
        $product_details->save();
    }

    public static function createProductColors($product_id, $request) {
        foreach(explode(",", $request->color_id) as $key => $color) {
            
            $product_color = ProductColor::create([
                'product_id' => $product_id,
                'color_id' => $color,
                'sku' => explode(",", $request->sku)[$key]
            ]);

            self::createProductImages($product_color->id, $key, $request);
            self::createProductCategories($product_color->id, $key, $request);
        }
    }

    public static function updateProductColors($product_id, $request) {
        $productColors = ProductColor::where('product_id', $product_id)->get();

        foreach($productColors as $productColor) {
            $products_images = ProductImage::where('product_color_id', $productColor->id)->get();

            foreach($products_images as $products_image) {
                if($products_image->image)
                    deleteFile($products_image->image);
            }

            $productColor->delete();
        }

        foreach(explode(",", $request->color_id) as $key => $color) {
            
            $product_color = ProductColor::create([
                'product_id' => $product_id,
                'color_id' => $color,
                'sku' => explode(",", $request->sku)[$key]
            ]);

            self::createProductImages($product_color->id, $key, $request);
            self::createProductCategories($product_color->id, $key, $request);
        }
    }

    public static function createProductTags($product_id, $request) {
        foreach(explode(",", $request->tag_id) as $tag_id) {
            ProductTag::create([
                'product_id' => $product_id,
                'tag_id' => $tag_id
            ]);
        }
    }

    public static function updateProductTags($product_id, $request) {
        ProductTag::where('product_id', $product_id)->delete();

        foreach(explode(",", $request->tag_id) as $tag_id) {
            ProductTag::create([
                'product_id' => $product_id,
                'tag_id' => $tag_id
            ]);
        }
    }

    public static function createProductImages($product_color_id, $key, $request) {
        if ($request->hasFile('images_'.$key)) {
            $images = $request->file('images_'.$key);
                
            foreach ($images as $image) {

                $path = 'products/gallery/';
        
                $file_data = uploadFile($image, $path);

                $product_image = ProductImage::create([
                        'product_color_id' => $product_color_id,
                        'image' => $file_data['filePath']
                    ]);
            }
        }
    }

    public static function createProduct($request) {
 
        $product = self::create([
            'user_id' => Auth::user()->getRoleNames()[0] === 'Proveedor' ? Auth::user()->id : 1,
            'brand_id' => $request->brand_id,
            'state_id' => Auth::user()->getRoleNames()[0] === 'Proveedor' ? 4 : 3,
            'name' => $request->name,
            'single_description' => $request->single_description === 'null' ? null : $request->single_description,
            'description' => $request->description === 'null' ? null : $request->description,
            'price' => $request->price,
            'price_for_sale' => $request->price_for_sale,
            'wholesale_price' => $request->wholesale_price,
            'stock' => $request->stock,
            'slug' => Str::slug($request->name)
        ]);

        self::createProductDetails($product->id, $request);
        self::createProductTags($product->id, $request);
        self::createProductColors($product->id, $request);

        return $product;
    }
 
    public static function updateProduct($request, $product) {
 
        $product->update([
            'brand_id' => $request->brand_id,
            'state_id' => 3,
            'name' => $request->name,
            'single_description' => $request->single_description === 'null' ? null : $request->single_description,
            'description' => $request->description === 'null' ? null : $request->description,
            'price' => $request->price,
            'price_for_sale' => $request->price_for_sale,
            'wholesale_price' => $request->wholesale_price,
            'stock' => $request->stock,
            'slug' => Str::slug($request->name)
        ]);

        self::updateProductDetails($product->id, $request);
        self::updateProductTags($product->id, $request);
        self::updateProductColors($product->id, $request);
 
        return $product;
    }
 
    public static function deleteProducts($ids) {
        foreach ($ids as $id) {
            $product = self::find($id);
            $product->state_id = 5;
            $product->update();
            $product->delete();
        }
    }

    public static function updateStatusProduct($request, $field, $product) {

        $favourite = 0;
        $archived = 0;
        $discarded = 0;

        if($field === 'favourite'){
            $favourite = ($request->input($field) === '1') ? 0 : 1;
            $archived = 0;
            $discarded = 0;
        } else if($field === 'archived'){
            $archived = ($request->input($field) === '1') ? 0 : 1;
            $favourite = 0;
            $discarded = 0;
        } else {
            $discarded = ($request->input($field) === '1') ? 0 : 1;
            $favourite = 0;
            $archived = 0;
        }

        $product->update([
            'favourite' => $favourite,
            'archived' => $archived,
            'discarded' => $discarded
        ]);  

        return $product;
    }

    public static function updateStatesProduct($request, $product) {

        $product->update([
            'state_id' => $request->state_id
        ]);  

        return $product;
    }
}
