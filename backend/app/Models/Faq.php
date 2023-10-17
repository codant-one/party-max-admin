<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\FaqCategory;

class Faq extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function category() {
        return $this->belongsTo(FaqCategory::class, 'faq_category_id', 'id');
    }

    /**** Scopes ****/
    public function scopeWhereSearch($query, $search) {
       $query->where('title', 'LIKE', '%' . $search . '%')
             ->orWhere('description', 'LIKE', '%' . $search . '%');
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
    public static function createFaq($request) {
        $faq = self::create([
            'faq_category_id' => $request->faq_category_id,
            'title' => $request->title,
            'description' => $request->description
        ]);

        return $faq;
    }

    public static function updateFaq($request, $faq) {
        $faq->update([
            'faq_category_id' => $request->faq_category_id,
            'title' => $request->title,
            'description' => $request->description
        ]);

        return $faq;
    }

    public static function deleteFaq($id) {
        self::deleteFaqs(array($id));
    }

    public static function deleteFaqs($ids) {
        foreach ($ids as $id) {
            $faq = self::find($id);
            $faq->delete();
        }
    }
}
