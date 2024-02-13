<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Document extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**** Public methods ****/

    public static function createDocument($request) {
        $document = self::create([
            'nit' => $request->nit,
            'rut' => $request->rut,
            'bank_account' => $request->bank_account
        ]);

        return $document;
    }

}
