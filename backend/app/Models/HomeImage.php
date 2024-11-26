<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class HomeImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Public methods ****/
    public static function createHomeImage($request) {
        
        $home_image = self::create([
            'is_slider' => $request->is_slider,
            'url' => $request->url
        ]);

        return $home_image;
    }

    public static function updateHomeImage($request, $home_image) {

        $home_image->update([
            'is_slider' => $request->is_slider,
            'url' => $request->url
        ]);

        return $home_image;
    }

    public static function deleteHomeImage($ids) {
        foreach ($ids as $id) {
            $home_image = self::find($id);
            $home_image->delete();

            if($home_image->image)
                deleteFile($home_image->image);
        }
            
    }

    /**** Scopes ****/
    public function scopeWhereSearch($query, $search) {
        $query->where('url', 'LIKE', '%' . $search . '%');
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy) {
        $query->orderBy($orderByField, $orderBy);
    }
    
    public function scopeApplyFilters($query, array $filters) {
        $filters = collect($filters);

        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }

        if ($filters->get('is_slider')) {
            $query->where('is_slider', intval($filters->get('is_slider')) === 2 ? 0 : 1);
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'created_at';
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

}
