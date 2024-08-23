<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cupcake extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function cake_size()
    {
        return $this->belongsTo(CakeSize::class, 'cake_size_id','id');
    }
}
