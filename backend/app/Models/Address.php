<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\AddressesType;

class Address extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function type() {
        return $this->belongsTo(AddressesType::class, 'addresses_type_id', 'id');
    }
}
