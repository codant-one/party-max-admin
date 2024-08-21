<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Service;

class ServiceLike extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function service() {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**** Public methods ****/
    public static function addFavorite($request) {
        
        $favorite = ServiceLike::updateOrInsert(
            [
                'user_id' => $request->user_id,
                'service_id' => $request->service_id
            ],
            [   
                'date' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        return $favorite;
    }

    public static function deleteFavorite($request) {

        ServiceLike::where([
            ['user_id', $request->user_id],
            ['service_id', $request->service_id]]
        )->delete(); 
    }
   
}
