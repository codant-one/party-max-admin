<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File; 

use App\Models\BlogCategory;
use App\Models\BlogTag;

class Blog extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function category() {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function tags()
    {
        return $this->hasMany(BlogTag::class, 'blog_id');
    }

    /**** Public methods ****/
    public static function createBlogTags($blog_id, $request) {
        foreach(explode(",", $request->tag_id) as $tag_id) {
            BlogTag::create([
                'blog_id' => $blog_id,
                'tag_id' => $tag_id
            ]);
        }
    }

    public static function createBlog($request) {

        $blog = self::create([
            'blog_category_id' => $request->blog_category_id,
            'user_id' => auth()->user()->id,
            'is_popular_blog' => $request->is_popular_blog,
            'date' => $request->date,
            'title' => $request->title,
            'description' =>  $request->description,
            'slug' => Str::slug($request->title)
        ]);

        self::createBlogTags($blog->id, $request);

        return $blog;
    }

    public static function updateBlogTags($blog_id, $request) {
        BlogTag::where('blog_id', $blog_id)->delete();

        foreach(explode(",", $request->tag_id) as $tag_id) {
            BlogTag::create([
                'blog_id' => $blog_id,
                'tag_id' => $tag_id
            ]);
        }
    }

    public static function updateBlog($request, $blog) {

        $blog->update([
            'blog_category_id' => $request->blog_category_id,
            'user_id' => auth()->user()->id,
            'is_popular_blog' => $request->is_popular_blog,
            'date' => $request->date,
            'title' => $request->title,
            'description' =>  $request->description,
            'slug' => Str::slug($request->title)
        ]);

        self::updateBlogTags($blog->id, $request);

        return $blog;
    }

    public static function deleteBlog($id) {
        $blog = self::find($id);
        $blog->delete();

        if($blog->image)
            deleteFile($blog->image);
    }

    /**** Scopes ****/
    public function scopeWhereSearch($query, $search) {
        $query->where('title', 'LIKE', '%' . $search . '%')
              ->orWhere('description', 'LIKE', '%' . $search . '%');
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
