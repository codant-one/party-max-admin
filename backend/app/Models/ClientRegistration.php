<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ClientRegistration extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    /**** Relationship ****/
    public function ip() {
        return $this->belongsTo(ClientIp::class, 'ip_id', 'id');
    }
    
}
