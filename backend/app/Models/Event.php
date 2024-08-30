<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Category;
use App\Models\State;

use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function service() {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    public function category() {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function state() {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    /**** Public methods ****/
    public static function createEvent($request) {

        $request = self::prepareRequest($request);
        $category_id = Category::where('name', $request->calendar)->first()->id;

        $event = self::create([
            'category_id' => $category_id,
            'default_date' => $request->default_date,
            'state_id' => $request->state_id,
            'title' => $request->title,
            'all_day' =>  1,
            'start_date' => $request->start,
            'end_date' => $request->end,
            'description' => $request->description
        ]);

        return $event;
    }

    public static function updateEvent($request, $event) {

        $request = self::prepareRequest($request);
        $category_id = Category::where('name', $request->calendar)->first()->id;

        $event->update([
            'category_id' => $category_id,
            'default_date' => $request->default_date,
            'state_id' => $request->state_id,
            'title' => $request->title,
            'all_day' =>  1,
            'start_date' => $request->start,
            'end_date' => $request->end,
            'description' => $request->description
        ]);

        return $event;
    }

    public static function deleteEvents($id) {
        $event = self::find($id);
        $event->delete();
    }

    public static function prepareRequest($request) {

        $message =
            'Tarea: ' . $request->title ;

        $request->merge(['message' => $message ]);
        $request->merge(['calendar' => $request->extendedProps['calendar']]);
        $request->merge(['description' => $request->extendedProps['description']]);
        $request->merge(['state_id' => $request->extendedProps['state_id']]);
        $request->merge(['state_id' => $request->extendedProps['service_id']]);

        $request->request->remove('extendedProps.calendar');
        $request->request->remove('extendedProps.description');
        $request->request->remove('extendedProps.state_id');
        $request->request->remove('extendedProps.service_id');

        return $request;
    }

    /**** Scopes ****/
    public function scopeWhereSearch($query, $search) {
        foreach (explode(' ', $search) as $term) {
            $query->where('title', 'LIKE', '%' . $term . '%')
                  ->orWhere('text', 'LIKE', '%' . $term . '%');
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
