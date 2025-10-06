<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Review extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function product() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function client() {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

     /**** Public methods ****/
    public static function createReview($request) {
        $data = [
            'client_id' => $request->client_id,
            'rating' => $request->rating,
            'date' => now(),
            'comments' => $request->comments === 'null' ? null : $request->comments
        ];

        if ($request->has('product_id')) {
            $data['product_id'] = $request->product_id;
        } elseif ($request->has('service_id')) {
            $data['service_id'] = $request->service_id;
        }

        $review = self::create($data);

        return $review;
    }

    public static function updateReview($request, $review) {
        $review->update([
            'product_id' => $request->product_id,
            'client_id' => $request->client_id,
            'rating' => $request->rating,
            'date' => now(),
            'comments' => $request->comments === 'null' ? null : $request->comments
        ]);

        return $review;
    }

    public static function deleteReviews($ids) {
        foreach ($ids as $id) {
            $review = self::find($id);
            $review->delete();
        }
    }

}
