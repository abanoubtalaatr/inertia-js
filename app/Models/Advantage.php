<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Advantage extends Model implements TranslatableContract
{
    use Translatable;

    protected $fillable = ['image'];

    public $translatedAttributes = ['title', 'description'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset("storage/{$this->image}") : null;
    }
}
