<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Province extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    /**** Relationship ****/
    public function country() {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

}
