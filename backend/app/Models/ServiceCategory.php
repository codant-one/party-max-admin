<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ServiceCategory extends Model
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

}
