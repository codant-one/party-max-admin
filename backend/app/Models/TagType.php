<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Tag;

class TagType extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**** Relationship ****/
     public function tags() {
        return $this->hasMany(Tag::class, 'tag_type_id', 'id');
    }

}
