<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use App\Models\ProductCategory;
use App\Models\ProductDetail;
use App\Models\ProductImage;
use App\Models\ProductColor;
use App\Models\ProductTag;
use App\Models\ProductList;
use App\Models\User;
use App\Models\Brand;
use App\Models\State;
use App\Models\Review;
use App\Models\OrderDetail;

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

    public function order()
    {
        return $this->hasMany(ProductList::class, 'product_id','id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id','id');
    }

    // Relación con los detalles de las órdenes (order_details)
    public function orderDetails()
    {
        return $this->hasManyThrough(OrderDetail::class, ProductColor::class, 'product_id', 'product_color_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($product) {
            $product->productlike()->delete();
        });
    }

    /**** Scopes ****/
    public function scopeSales($query, $date = null)
    {
        return $query->addSelect(['count_sales' => function($q) use ($date) {
            $q->selectRaw('count(p.id)')
              ->from('products as p')
              ->join('product_colors as pc', 'p.id', '=', 'pc.product_id')
              ->join('order_details as od', 'pc.id', '=', 'od.product_color_id')
              ->join('orders as o', 'o.id', '=', 'od.order_id')
              ->whereColumn('p.id', 'products.id')
              ->where([
                ['p.user_id', Auth::id()],
                ['o.payment_state_id', 4]
              ]);

              if($date !== null) {
                if(count($date) === 2)
                    $q->whereBetween('o.date', $date);
                else 
                    $q->where('o.date', $date[0]);
              }
        }]);
    }

    public function scopeIsFavorite($query)
    {
        return $query->addSelect(['is_favorite' => function($q) {
            if (Auth::check()) {
                $q->selectRaw('count(*)')
                    ->from('product_likes')
                    ->whereColumn('product_id', 'products.id')
                    ->where('user_id', Auth::id());
            } else {
                $q->selectRaw('0');
            }
        }]);
   
    }

    public function scopeBestSellers($query)
    {
        return  $query->addSelect(['count' => function ($q) {
                    $q->selectRaw('count(p.id)')
                      ->from('products as p')
                      ->join('product_colors as pc', 'p.id', '=', 'pc.product_id')
                      ->join('order_details as od', 'pc.id', '=', 'od.product_color_id')
                      ->whereColumn('p.id', 'products.id')
                      ->groupBy('p.id');
                }]);   
    }

    public function scopeOrder($query, $categoryId = null)
    {
        if(!is_null($categoryId))
            return  $query->addSelect(['category_order_id' => function ($q) use ($categoryId) {
                        $q->selectRaw('pl.order_id')
                        ->from('products as p')
                        ->join('product_lists as pl', 'p.id', '=', 'pl.product_id')
                        ->where('pl.category_id', $categoryId)
                        ->whereColumn('p.id', 'products.id');
                    }]);
   
    }

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
              ->orWhereHas('colors', function ($q) use ($search) {
                $q->where('sku', 'LIKE', '%' . $search . '%');
        });
    }

    public function scopeWhereSearchPublic($query, $search) {
        $query->where('products.name', 'LIKE', '%' . $search . '%');
    }

    public function scopeWhereCategory($query, $search) {
        $query->whereHas('colors.categories', function ($q) use ($search) {
            $q->where('category_id', $search);
        });
    }

    public function scopeWhereCategorySlug($query, $search) {

        $query->select('products.*')
              ->join('product_lists as pl', 'pl.product_id', '=', 'products.id')
              ->join('categories as c', 'c.id', '=', 'pl.category_id')
              ->where('c.slug', 'LIKE', '%' . $search);
    }

    public function scopeWhereColor($query, $colorIds) {
        $query->whereHas('colors', function ($q) use ($colorIds) {
            $q->whereIn('color_id', $colorIds);
        });
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy, $filters) {

        if($filters->get('sortBy')) {            
            if($filters->get('sortBy') === 0) 
                $query->orderByRaw('(IFNULL('. $orderByField .', products.id)) '. $orderBy);
            else {
                $wholesalersActive = $filters->get('wholesalers') === 'true';
                $orderByField = $wholesalersActive ? 'wholesale_price' : 'price_for_sale';

                switch ($filters->get('sortBy')) {
                    case 1:
                        $query->orderByRaw("CAST($orderByField AS DECIMAL(10,2)) ASC");
                        break;
                    case 2:
                        $query->orderByRaw("CAST($orderByField AS DECIMAL(10,2)) DESC");
                        break;
                    case 3:
                        $query->orderBy('rating', 'desc');
                        break;
                    case 4:
                        $query->orderBy('products.created_at', 'desc');
                        break;
                }
            }
        } else
            $query->orderByRaw('(IFNULL('. $orderByField .', products.id)) '. $orderBy);
    }
 
    public function scopeApplyFilters($query, array $filters) {
        $filters = collect($filters);

        if(Auth::check() && Auth::user()->getRoleNames()[0] === 'Proveedor') {
            $query->where('user_id', Auth::user()->id);
        }
 
        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }

        if ($filters->get('searchPublic')) {
            $query->whereSearchPublic($filters->get('searchPublic'));
        }

        if ($filters->get('favourite') !== null) {
            $query->where('favourite', $filters->get('favourite'));
        }
        
        if($filters->get('supplierId')) {
            $query->where('user_id', $filters->get('supplierId'));
        }

        if($filters->get('isSales')) {
            $query->having('count_sales', '>', 0);
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

        if ($filters->get('subcategory') !== null) {
            $query->whereCategorySlug($filters->get('subcategory'));
        } else if ($filters->get('category') !== null && $filters->get('category') !== 'all') {
            $query->whereCategorySlug($filters->get('category'));
        }

        if($filters->get('colorId') !== null){
            $colorIds = explode(',', $filters->get('colorId'));
            $query->whereColor($colorIds);
        }

        if($filters->get('min') !== null && $filters->get('max') !== null) {
            if ($filters->get('wholesalers') && $filters->get('wholesalers') === 'true')
                $query->whereBetween(\DB::raw('CAST(wholesale_price AS DECIMAL(10,2))'),[$filters->get('min'), $filters->get('max')]);
            else
                $query->whereBetween(\DB::raw('CAST(price_for_sale AS DECIMAL(10,2))'),[$filters->get('min'), $filters->get('max')]);
        }

        if ($filters->get('wholesalers') && $filters->get('wholesalers') === 'true') {
            $query->whereNotNull('wholesale_price');
        }

        if ($filters->get('type_sales')) {
            if ($filters->get('type_sales') === '2' ) {
                $query->whereNotNull('wholesale_price');
            } else {
                $query->whereNull('wholesale_price'); 
            }
        }

        if ($filters->get('isFilterPublic') && $filters->get('isFilterPublic') === 'true') {
            if ($filters->get('type_sales') === '2' ) {
                $query->whereNotNull('wholesale_price');
            } else {
                $query->whereNull('wholesale_price'); 
            }
        }

        if($filters->get('rating') !== null){
            $query->where('rating', '<=', doubleval($filters->get('rating')));
        }
                
        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'id';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy, $filters);
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

    public static function createProductOrder($product_id) {
        ProductList::where('product_id', $product_id)->delete();
        
        $product = Product::with('colors.categories')->find($product_id);
        
        $categories = collect($product->colors)
            ->flatMap(function ($color) {
                return $color->categories;
            })
            ->unique('category_id')
            ->values()
            ->toArray();
        
        foreach($categories as $key => $category) {
            $order_id = 
                ProductList::where('category_id', $category['category_id'])
                           ->latest('order_id')
                           ->first()
                           ->order_id ?? 0;

            ProductList::create([
                'product_id' => $product_id,
                'category_id' => $category['category_id'],
                'order_id' => $order_id ? $order_id + 1 : 1
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

                $products_image->delete();
            }
        }

        foreach(explode(",", $request->color_id) as $key => $color) {
            
            $product_color = ProductColor::where([['product_id', $product_id], ['color_id', $color]])->first();

            if($product_color)
                $product_color->update([
                    'product_id' => $product_id,
                    'color_id' => $color,
                    'sku' => explode(",", $request->sku)[$key]
                ]);
            else 
                $product_color = ProductColor::create([
                    'product_id' => $product_id,
                    'color_id' => $color,
                    'sku' => explode(",", $request->sku)[$key]
                ]);

            $product_color->categories()->delete();

            self::createProductImages($product_color->id, $key, $request);
            self::createProductCategories($product_color->id, $key, $request);
        }

        $colors = 
            ProductColor::where('product_id', $product_id)
                        ->whereNotIn('color_id', explode(",", $request->color_id))
                        ->get();    

        foreach($colors as $item) {
            
            $color = ProductColor::find($item->id);
                
            if($color)
                $color->delete();
                         
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
 
        $user_id = 
            ($request->user_id === '0') ? 
            (Auth::user()->getRoleNames()[0] === 'Proveedor' ? Auth::user()->id : 1) : 
            $request->user_id;

        $product = self::create([
            'user_id' => $user_id,
            'brand_id' => $request->brand_id,
            'state_id' => Auth::user()->getRoleNames()[0] === 'Proveedor' ? 4 : 3,
            'name' => $request->name,
            'single_description' => $request->single_description === 'null' ? null : $request->single_description,
            'description' => $request->description === 'null' ? null : $request->description,
            'price' => $request->price,
            'price_for_sale' => $request->price_for_sale,
            'wholesale' => $request->wholesale,
            'wholesale_price' => $request->wholesale_price === 'null' ? null : $request->wholesale_price,
            'wholesale_min' => $request->wholesale_min,
            'stock' => $request->stock,
            'slug' => Str::slug($request->name)
        ]);

        self::createProductDetails($product->id, $request);
        self::createProductTags($product->id, $request);
        self::createProductColors($product->id, $request);
        self::createProductOrder($product->id);

        return $product;
    }
 
    public static function updateProduct($request, $product) {
 
        $user_id = 
            ($request->user_id === '0') ? 
            (Auth::user()->getRoleNames()[0] === 'Proveedor' ? Auth::user()->id : 1) : 
            $request->user_id;

        $product->update([
            'user_id' => $user_id,
            'brand_id' => $request->brand_id,
            'state_id' => Auth::user()->getRoleNames()[0] === 'Proveedor' ? 4 : 3,
            'name' => $request->name,
            'single_description' => $request->single_description === 'null' ? null : $request->single_description,
            'description' => $request->description === 'null' ? null : $request->description,
            'price' => $request->price,
            'price_for_sale' => $request->price_for_sale,
            'wholesale' => $request->wholesale,
            'wholesale_price' => ($request->wholesale_price === 'null' || $request->wholesale === '0') ? null : $request->wholesale_price,
            'wholesale_min' => $request->wholesale_min,
            'stock' => $request->stock,
            'in_stock' => (intval($request->stock) >= 1) ? 1 : 0,
            'slug' => Str::slug($request->name)
        ]);

        self::updateProductDetails($product->id, $request);
        self::updateProductTags($product->id, $request);
        self::updateProductColors($product->id, $request);
        self::createProductOrder($product->id);

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

    public static function calculateRating($product_id) {

        $reviews = Review::where('product_id', $product_id)->get();
        $sum = 0;

        foreach($reviews as $review){
            $sum = $sum + $review->rating;
        }

        $product = Product::find($product_id);
        $product->rating = $sum/(count($reviews));//average
        $product->update();
    }

    public static function updateStatusProduct($field, $product) {

        $favourite = 0;
        $archived = 0;
        $discarded = 0;

        if($field === 'favourite'){
            $favourite = !$product->favourite;
            $archived = 0;
            $discarded = 0;
        } else if($field === 'archived'){
            $archived = !$product->archived;
            $favourite = 0;
            $discarded = 0;
        } else {
            $discarded = !$product->discarded;
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

    public static function updateStockProduct($product, $quantity) {
        $new_stock = $product->stock - $quantity;
    
        $product->update([
            'stock' => $new_stock,
            'in_stock' => ($new_stock === 0) ? 0 : 1
        ]);

        if(intval($new_stock) < 3 && intval($new_stock) !== 0)
            self::sendMail($product, 1);
        else if(intval($new_stock) === 0)
            self::sendMail($product, 2);
    }

    public static function sendMail($product, $type) {

        $email = $product->user->email;

        if($type === 1) {
            $subject = '('. $product->user->name . ' ' . $product->user->last_name . ') tienes poca existencia, monitorea tu producto.';
            $view = 'emails.suppliers.little_product_existence';
        } else {
            $subject = '('. $product->user->name . ' ' . $product->user->last_name . ') inventario agotado.';
            $view = 'emails.suppliers.out_of_stock';
        }

        $link = env('APP_DOMAIN_ADMIN').'/dashboard/products/products';

        $productInfo = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_image' => asset('storage/' . $product->image),
            'slug' =>env('APP_DOMAIN_ADMIN').'/dashboard/products/products/edit/'.$product->id,
            'stock' => $product->stock . ((intval($product->stock) > 1) ? ' Unidades' : ' Unidad'),
        ];

        $data = [
            'product' => $productInfo,
            'link' => $link
        ];

        try {
            \Mail::send(
                $view
                , ['data' => $data]
                , function ($message) use ($email, $subject) {
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    $message->to($email)->subject($subject);
            });
        } catch (\Exception $e){
            $message = 'error';
            $responseMail = $e->getMessage();

            Log::info($message . ' ' . $responseMail);
        } 
    }
}
