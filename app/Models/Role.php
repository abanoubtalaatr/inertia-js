<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole implements TranslatableContract
{
    use Translatable;

    public $translatedAttributes = [];

    protected $fillable = ['guard_name', 'is_active', 'name'];

    public function logs(): HasMany
    {
        return $this->hasMany(
            \App\Models\Log::class,
            'by_user_id'
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
