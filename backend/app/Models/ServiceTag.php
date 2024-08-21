<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\Tag;

class ServiceTag extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
    public function tag()
    {
        return $this->belongsTo(Tag::class, 'tag_id','id');
    }

}
