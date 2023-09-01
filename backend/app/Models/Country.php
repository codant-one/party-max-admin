<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Country extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    /**** Relationship ****/
    public function provinces() {
        return $this->hasMany(Province::class, 'country_id', 'id');
    }

}
