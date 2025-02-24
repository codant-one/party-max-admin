<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ClientIp extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    /**** Relationship ****/
    public function client (){
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }
}
