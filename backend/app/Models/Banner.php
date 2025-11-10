<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class Banner extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    

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

    /**** Public methods ****/

    public static function createBanner($request) {

        $banner = self::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'banner' => '',
            'banner_2' => '',
            'banner_3' => '',
            'banner_4' => ''
        ]);

        return $banner;
    }

    public static function updateBanner($request, $banner) {

        $banner->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return $banner;
    }

    public static function deleteBanners($ids) {
        foreach ($ids as $id) {
            $banner = self::find($id);
            $banner->delete();

            if($banner->banner) {
                deleteFile($banner->banner);
                deleteFile($banner->banner_2);
                deleteFile($banner->banner_3);
                deleteFile($banner->banner_4);
            }
        }
    }
}
