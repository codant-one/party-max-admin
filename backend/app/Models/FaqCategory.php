<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Faq;

class FaqCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function faqs() {
        return $this->hasMany(Faq::class, 'faq_category_id', 'id');
    }

    /**** Scopes ****/
    public function scopeWhereSearch($query, $search) {
        foreach (explode(' ', $search) as $term) {
            foreach (explode(' ', $search) as $term) {
                $query->where('name', 'LIKE', '%' . $term . '%')
                      ->orWhere('description', 'LIKE', '%' . $term . '%');
            }
        }
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
    public static function createCategory($request) {
        $slug = Str::slug($request->name);
        $permissions = 'ver '.$slug.'-category';

        $category = self::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'icon' => $request->icon,
            'color' => $request->color
        ]);

        return $category;
    }

    public static function updateCategory($request, $category) {
        $slug = Str::slug($request->name);
        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
            'color' => $request->color
        ]);

        return $category;
    }

    public static function deleteCategories($ids) {
        foreach ($ids as $id) {
            $category = self::find($id);
            $category->delete();
        }
    }
}
