<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Models\Brand;
use App\Models\State;
use App\Models\ServiceTag;
use App\Models\ServiceList;
use App\Models\ServiceVideo;
use App\Models\Cupcake;

class Service extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    /**** Relationship ****/
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

    public function categories()
    {
        return $this->hasMany(ServiceCategory::class, 'service_id');
    }

    public function images()
    {
        return $this->hasMany(ServiceImage::class, 'service_id');
    }
    
    public function tags()
    {
        return $this->hasMany(ServiceTag::class, 'service_id');
    }

    public function order()
    {
        return $this->hasMany(ServiceList::class, 'order_id','id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'service_id','id');
    }

    public function cupcakes()
    {
        return $this->hasMany(Cupcake::class, 'service_id');
    }
    
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'service_id');
    }

    public function firstCupcake()
    {
        return $this->hasOne(Cupcake::class, 'service_id','id')->orderBy('price');
    }

    public function videos()
    {
        return $this->hasMany(ServiceVideo::class, 'service_id','id');
    }

    /**** Scopes ****/
    public function scopeSales($query, $date = null) {
        return 
            $query->addSelect(['count_sales' => function($q) use ($date) {
                $q->selectRaw('SUM(od.quantity)')
                  ->from('services as s')
                  ->join('order_details as od', 's.id', '=', 'od.service_id')
                  ->join('orders as o', 'o.id', '=', 'od.order_id')
                  ->whereColumn('s.id', 'services.id')
                  ->where([
                    ['s.state_id', 3],
                    ['o.payment_state_id', 4]
                  ]);

                if($date !== null) {
                    if(count($date) === 2)
                        $q->whereBetween('o.date', $date);
                    else 
                        $q->where('o.date', $date[0]);
                }
            }])->addSelect(['sales_total' => function($q) use ($date) {
                $q->selectRaw('SUM(od.total)')
                  ->from('services as s')
                  ->join('order_details as od', 's.id', '=', 'od.service_id')
                  ->join('orders as o', 'o.id', '=', 'od.order_id')
                  ->whereColumn('s.id', 'services.id')
                  ->where([
                    ['s.state_id', 3],
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

    public function scopeOrder($query, $categoryId = null) {
        if(!is_null($categoryId))
            return  $query->addSelect(['category_order_id' => function ($q) use ($categoryId) {
                        $q->selectRaw('sl.order_id')
                        ->from('services as s')
                        ->join('service_lists as sl', 's.id', '=', 'sl.service_id')
                        ->where('sl.category_id', $categoryId)
                        ->whereColumn('s.id', 'services.id')
                        ->limit(1);
                    }]);
   
    }

    public function scopeSelling($query) {
        return  $query->addSelect(['selling_price' => function ($q){
                    $q->selectRaw('COUNT(s.id)')
                        ->from('services as s')
                        ->leftJoin('order_details as od', 'od.service_id', '=', 's.id')
                        ->leftJoin('orders as o', 'od.order_id', '=', 'o.id')
                        ->where(function ($query) {
                            $query->where('o.shipping_state_id', 3)
                                  ->orWhere('o.shipping_state_id', 4);
                        })
                        ->whereColumn('s.id', 'services.id');
                }]);
    }

    public function scopeSalesPrice($query) {
        return  $query->addSelect(['sales_price' => function ($q){
                $q->selectRaw('SUM(CAST(od.total AS DECIMAL(10, 2)))')
                        ->from('services as s')
                        ->leftJoin('order_details as od', 'od.service_id', '=', 's.id')
                        ->leftJoin('orders as o', 'od.order_id', '=', 'o.id')
                        ->where('o.payment_state_id', 4)
                        ->whereColumn('s.id', 'services.id');
                }]);
    }

    public function scopeComments($query) {
        return  $query->addSelect(['comments' => function ($q){
                    $q->selectRaw('COUNT(s.id)')
                        ->from('services as s')
                        ->join('reviews as r', 'r.service_id', '=', 's.id')
                        ->whereColumn('s.id', 'services.id');
                }]);
    }

    public function scopeFavorites($query) {
        return  $query->addSelect(['likes' => function ($q){
                    $q->selectRaw('COUNT(s.id)')
                      ->from('services as s')
                      ->join('service_likes as s_l', 's.id', '=', 's_l.service_id')
                      ->whereColumn('s.id', 'services.id');
                }]);
    }

    public function scopeIsFavorite($query) {
        return $query->addSelect(['is_favorite' => function($q) {
            if (Auth::check()) {
                $q->selectRaw('count(*)')
                    ->from('service_likes')
                    ->whereColumn('service_id', 'services.id')
                    ->where('user_id', Auth::id());
            } else {
                $q->selectRaw('0');
            }
        }]);
   
    }

    public function scopeStore($query) {
        return  $query->addSelect(['store' => function ($q){
                    $q->selectRaw('d.store_name')
                        ->from('services as s')
                        ->join('users as u', 'u.id', '=', 's.user_id')
                        ->join('user_details as d', 'u.id', '=', 'd.user_id')
                        ->whereColumn('s.id', 'services.id');
                }]);
    }

    public function scopeCompany($query) {
        return  $query->addSelect(['company' => function ($q){
                    $q->selectRaw('su.company_name')
                        ->from('services as s')
                        ->join('users as u', 'u.id', '=', 's.user_id')
                        ->join('suppliers as su', 'u.id', '=', 'su.user_id')
                        ->whereColumn('s.id', 'services.id');
                }]);
    }

    public function scopeUserService($query) {
        return  $query->addSelect(['user' => function ($q){
                    $q->selectRaw("CONCAT(u.name, ' ', u.last_name)")
                        ->from('services as s')
                        ->join('users as u', 'u.id', '=', 's.user_id')
                        ->whereColumn('s.id', 'services.id');
                }]);
    }

    public function scopeWhereSearch($query, $search) {
        $query->where('name', 'LIKE', '%' . $search . '%')
              ->orWhere('sku', 'LIKE', '%' . $search . '%');
    }

    public function scopeWhereCategory($query, $search) {
        $query->whereHas('categories', function ($q) use ($search) {
            $q->where('category_id', $search);
        });
    }

    public function scopeWhereCategorySlug($query, $search) {
        if (!$search) {
            return $query;
        }

        $query->addSelect('sl.order_id')
              ->join('service_lists as sl', 'sl.service_id', '=', 'services.id')
              ->join('categories as c', 'c.id', '=', 'sl.category_id')
              ->where('c.slug', $search)
              ->limit(1);
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy, $filters) {

        if($filters->get('sortBy')) {            
            if($filters->get('sortBy') === 0) 
                $query->orderByRaw('(IFNULL('. $orderByField .', services.id)) '. $orderBy);
            else {
                switch ($filters->get('sortBy')) {
                    case 1:
                        $query->orderByRaw("CAST(price AS DECIMAL(10,2)) ASC");
                        break;
                    case 2:
                        $query->orderByRaw("CAST(price AS DECIMAL(10,2)) DESC");
                        break;
                    case 3:
                        $query->orderBy('rating', 'desc');
                        break;
                    case 4:
                        $query->orderBy('services.created_at', 'desc');
                        break;
                }
            }
        } else
            $query->orderByRaw('(IFNULL('. $orderByField .', services.id)) '. $orderBy);
    }

    public function scopeApplyFilters($query, array $filters) {
        $filters = collect($filters);

        if(Auth::check() && Auth::user()->getRoleNames()[0] === 'Proveedor') {
            $query->where('user_id', Auth::user()->id);
        } else {
            if($filters->get('supplier_id')) {
                $query->where('user_id', $filters->get('supplier_id'));
            }
        }
 
        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }

        if($filters->get('isSales')) {
            $query->having('count_sales', '>', 0);
        }

        if ($filters->get('favourite') !== null) {
            $query->where('favourite', $filters->get('favourite'));
        }
        
        if($filters->get('supplierId')) {
            $query->where('user_id', $filters->get('supplierId'));
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

        if ($filters->get('category_id') !== null) {
            $query->whereCategory($filters->get('category_id'));
        }

        if ($filters->get('fathercategory') !== null && $filters->get('category') !== null && $filters->get('subcategory') !== null) {
            $query->whereCategorySlug($filters->get('category'). '/' . $filters->get('fathercategory') . '/' . $filters->get('subcategory'));
        } else if ($filters->get('subcategory') !== null && $filters->get('category') !== null) {
            $query->whereCategorySlug($filters->get('category'). '/' . $filters->get('subcategory'));
        } else if ($filters->get('category') !== null) {
            $query->whereCategorySlug($filters->get('category'));
        }

        if($filters->get('colorId') !== null){
            $colorIds = explode(',', $filters->get('colorId'));
            $query->whereColor($colorIds);
        }

        if($filters->get('min') !== null && $filters->get('max') !== null) {
            $query->whereBetween(\DB::raw('CAST(price AS DECIMAL(10,2))'),[$filters->get('min'), $filters->get('max')]);
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
    public static function createServiceOrder($service_id) {
        ServiceList::where('service_id', $service_id)->delete();
        
        $service = Service::with('categories')->find($service_id);
        
        foreach($service->categories as $key => $category) {
            $order_id = 
            ServiceList::where('category_id', $category->category_id)
                           ->latest('order_id')
                           ->first()
                           ->order_id ?? 0;

            ServiceList::create([
                'service_id' => $service_id,
                'category_id' => $category['category_id'],
                'order_id' => $order_id ? $order_id + 1 : 1
            ]);
        }
    }

    public static function createServiceTags($service_id, $request) {
        foreach(explode(",", $request->tag_id) as $tag_id) {
            ServiceTag::create([
                'service_id' => $service_id,
                'tag_id' => $tag_id
            ]);
        }
    }

    public static function createServiceCategories($service_id, $request) {
        foreach(explode(",", $request->category_id) as $category_id) {
            ServiceCategory::create([
                'service_id' => $service_id,
                'category_id' => $category_id
            ]);
        }
    }

    public static function createServiceImages($service_id, $request) {
        if ($request->hasFile('images')) {
            $images = $request->file('images');
                
            foreach ($images as $image) {

                $path = 'services/gallery/';
        
                $file_data = uploadFile($image, $path);

                $service_image = ServiceImage::create([
                    'service_id' => $service_id,
                    'image' => $file_data['filePath']
                ]);
            }
        }
    }

    public static function createCupcakes($service_id, $request) {
        $cupcakes = Cupcake::where('service_id', $service_id)->delete();

        foreach(explode(",", $request->cake_size_id) as $key => $cake_size_id) {
            $cupcake = Cupcake::create([
                'service_id' => $service_id,
                'cake_size_id' => $cake_size_id,
                'is_simple' => explode(",", $request->is_simple)[$key],
                'price' => explode(",", $request->prices)[$key]
            ]);
        }
    }

    public static function createServiceVideos($service_id, $request) {
        foreach(explode(",", $request->video) as $url) {
            if($url !== '' && $url !== null)
                ServiceVideo::create([
                    'service_id' => $service_id,
                    'url' => $url
                ]);
        }
    }
    
    public static function updateServiceVideos($service_id, $request) {
        ServiceVideo::where('service_id', $service_id)->delete();

        foreach(explode(",", $request->video) as $url) {
            if($url !== '' && $url !== null)
                ServiceVideo::create([
                    'service_id' => $service_id,
                    'url' => $url
                ]);
        }
    }

    public static function createService($request) {
 
        $user_id = 
            ($request->user_id === '0') ? 
            (Auth::user()->getRoleNames()[0] === 'Proveedor' ? Auth::user()->id : 1) : 
            $request->user_id;

        $service = self::create([
            'user_id' => $user_id,
            'brand_id' => $request->brand_id,
            'state_id' => Auth::user()->getRoleNames()[0] === 'Proveedor' ? 4 : 3,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'single_description' => $request->single_description === 'null' ? null : $request->single_description,
            'description' => $request->description === 'null' ? null : $request->description,
            'sku' => $request->sku,
            'price' => $request->price === 'null' ? null : $request->price,
            'is_full' => $request->is_full,
            'estimated_delivery_time' => $request->estimated_delivery_time
        ]);

        self::createServiceTags($service->id, $request);
        self::createServiceCategories($service->id, $request);
        self::createServiceImages($service->id, $request);
        self::createServiceOrder($service->id);
        self::createServiceVideos($service->id, $request);

        if($request->isCupcake === 'true')
            self::createCupcakes($service->id, $request);
        
            return $service;
    }

    public static function updateServiceTags($service_id, $request) {
        ServiceTag::where('service_id', $service_id)->delete();

        foreach(explode(",", $request->tag_id) as $tag_id) {
            ServiceTag::create([
                'service_id' => $service_id,
                'tag_id' => $tag_id
            ]);
        }
    }
    
    public static function updateServiceCategories($service_id, $request) {
        ServiceCategory::where('service_id', $service_id)->delete();

        foreach(explode(",", $request->category_id) as $category_id) {
            ServiceCategory::create([
                'service_id' => $service_id,
                'category_id' => $category_id
            ]);
        }
    }

    public static function updateServiceImages($service_id, $request) {
        $servicesImages = ServiceImage::where('service_id', $service_id)->get();
            
        foreach ($servicesImages as $servicesImage) {
            if($servicesImage->image)
                deleteFile($servicesImage->image);

            $servicesImage->delete();
        }

        if ($request->hasFile('images')) {
            $images = $request->file('images');

            foreach ($images as $image) {
                $path = 'services/gallery/';
        
                $file_data = uploadFile($image, $path);

                $service_image = ServiceImage::create([
                    'service_id' => $service_id,
                    'image' => $file_data['filePath']
                ]);
            }
        }
    }

    public static function updateService($request, $service) {
 
        $user_id = 
            ($request->user_id === '0') ? 
            (Auth::user()->getRoleNames()[0] === 'Proveedor' ? Auth::user()->id : 1) : 
            $request->user_id;

        $service->update([
            'user_id' => $user_id,
            'brand_id' => $request->brand_id,
            'state_id' => Auth::user()->getRoleNames()[0] === 'Proveedor' ? 4 : 3,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'single_description' => $request->single_description === 'null' ? null : $request->single_description,
            'description' => $request->description === 'null' ? null : $request->description,
            'sku' => $request->sku,
            'price' => $request->price === 'null' ? null : $request->price,
            'is_full' => $request->is_full,
            'estimated_delivery_time' => $request->estimated_delivery_time
        ]);

        self::updateServiceTags($service->id, $request);
        self::updateServiceCategories($service->id, $request);
        self::updateServiceImages($service->id, $request);
        self::updateServiceVideos($service->id, $request);
        self::createServiceOrder($service->id);

        if($request->isCupcake === 'true')
            self::createCupcakes($service->id, $request);
        
            return $service;

        return $service;
    }
    
    public static function updateStatusService($field, $service) {

        $favourite = 0;
        $archived = 0;
        $discarded = 0;

        if($field === 'favourite'){
            $favourite = !$service->favourite;
            $archived = 0;
            $discarded = 0;
        } else if($field === 'archived'){
            $archived = !$service->archived;
            $favourite = 0;
            $discarded = 0;
        } else {
            $discarded = !$service->discarded;
            $favourite = 0;
            $archived = 0;
        }

        $service->update([
            'favourite' => $favourite,
            'archived' => $archived,
            'discarded' => $discarded
        ]);  

        return $service;
    }

    public static function updateStatesService($request, $service) {

        $service->update([
            'state_id' => $request->state_id,
            'deleted_at' => null
        ]);  

        return $service;
    }

    public static function calculateRating($service_id) {

        $reviews = Review::where('service_id', $service_id)->get();
        $sum = 0;

        foreach($reviews as $review){
            $sum = $sum + $review->rating;
        }

        $service = Service::find($service_id);

        // Evita la división por cero
        if (count($reviews) > 0) {
            $service->rating = $sum / count($reviews);
        } else {
            $service->rating = null; 
        }
        
        $service->update();
    }

    public static function deleteServices($ids) {
        foreach ($ids as $id) {
            $service = self::find($id);
            $service->state_id = 5;
            $service->update();
            $service->delete();
        }
    }

}
