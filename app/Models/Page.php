<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Page extends Model implements TranslatableContract
{
    use Translatable;

    protected $fillable = ['slug', 'image'];

    public $translatedAttributes = ['title', 'description'];

    protected $appends = ['image_url'];

    public function sections()
    {
        return $this->hasMany(PageSection::class);
    }

    public function getImageUrlAttribute()
    {
        return asset("storage/{$this->attributes['image']}");
    }
}
