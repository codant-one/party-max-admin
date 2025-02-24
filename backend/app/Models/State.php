<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class State extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function clients() {
        return $this->hasMany(Client::class, 'state_id', 'id');
    }

    /**** Public methods ****/
    public static function forDropdown()
    {
        return DB::table('states as s')
            ->select(['s.id', 's.name' ])
            ->get()->pluck('name','id');
    }
}
