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
    
    /**** Scopes ****/
    public function scopeOrder($query, $categoryId = null)
    {
        if(!is_null($categoryId))
            return  $query->addSelect(['category_order_id' => function ($q) use ($categoryId) {
                        $q->selectRaw('sl.order_id')
                        ->from('services as s')
                        ->join('service_lists as sl', 's.id', '=', 'sl.service_id')
                        ->where('sl.category_id', $categoryId)
                        ->whereColumn('s.id', 'services.id');
                    }]);
   
    }

    public function scopeWhereSearch($query, $search) {
        $query->where('name', 'LIKE', '%' . $search . '%');
    }

    public function scopeWhereCategory($query, $search) {
        $query->whereHas('categories', function ($q) use ($search) {
            $q->where('category_id', $search);
        });
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy, $filters) {

        if($filters->get('sortBy')) {            
            if($filters->get('sortBy') === 0) 
                $query->orderByRaw('(IFNULL('. $orderByField .', services.id)) '. $orderBy);
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
            'price' => $request->price            
        ]);

        self::createServiceTags($service->id, $request);
        self::createServiceCategories($service->id, $request);
        self::createServiceImages($service->id, $request);

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
            'price' => $request->price
        ]);

        self::updateServiceTags($service->id, $request);
        self::updateServiceCategories($service->id, $request);
        self::updateServiceImages($service->id, $request);

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
            'state_id' => $request->state_id
        ]);  

        return $service;
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
