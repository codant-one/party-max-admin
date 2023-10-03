<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File; 

class Blog extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Public methods ****/
    public static function createBlog($request) {

        $blog = self::create([
            'title' => $request->title,
            'description' =>  $request->description
        ]);

        return $blog;
    }

    public static function updateBlog($request, $blog) {

        if($request->hasFile('image'))
        {
            $image = $request->file('image'); 
            $nameimage = time(). '-' .Str::slug($request->name)."_".$image->getClientOriginalName();
            $nameimage = str_replace(' ', '', $nameimage);
            $pathimage = $request->image->storeAs('images/blogs', $nameimage, 'public');
        }

        $blog->update([
            'title' => $request->title,
            'description' =>  $request->description,
            'image' => $pathimage
        ]);

        return $blog;
    }

    public static function deleteBlog($id) {
        $blog = self::find($id);
        $blog->delete();
    }

    /**** Scopes ****/
    public function scopeWhereSearch($query, $search) {
        foreach (explode(' ', $search) as $term) {
            $query->where('title', 'LIKE', '%' . $term . '%')
                  ->orWhere('description', 'LIKE', '%' . $term . '%');
        }
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy) {
        $query->orderBy($orderByField, $orderBy);
    }
    
    public function scopeApplyFilters($query, array $filters) {
        $filters = collect($filters);

        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
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
