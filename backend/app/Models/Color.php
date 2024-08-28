<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Color extends Model
{
    use HasFactory;

    protected $guarded = [];


    /**** Public methods ****/
    public static function createColor($request) {
        $color = self::create([
            'name' => $request->name
        ]);
 
        return $color;
    }
    public static function updateColor($request, $color) {
        $color->update([
            'name' => $request->name
        ]);
 
        return $color;
     }
 
    public static function deleteColor($id) {
        self::deleteColors(array($id));
    }
 
    public static function deleteColors($ids) {
        foreach ($ids as $id) {
            $color = self::find($id);
            $color->delete();
        }
    }
}
