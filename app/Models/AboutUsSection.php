<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AboutUsSection extends Model
{
    protected $fillable = ['image_path1', 'image_path2'];

    public function items(): HasMany
    {
        return $this->hasMany(AboutUsItem::class)->orderBy('order');
    }
}
