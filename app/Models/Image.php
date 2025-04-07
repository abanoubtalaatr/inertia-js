<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = ['imageable_type', 'imageable_id', 'path', 'type'];

    public function imageable()
    {
        return $this->morphTo();
    }
}
