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
            'order_id' => $request->order_id,
            'is_slider' => $request->is_slider,
            'image' => $request->image,
            'url' => $request->url
        ]);

        return $home_image;
    }

    public static function updateHomeImage($request, $home_image) {

        $home_image->update([
            'order_id' => $request->order_id,
            'is_slider' => $request->is_slider,
            'image' => $request->image,
            'url' => $request->url
        ]);

        return $home_image;
    }

}
