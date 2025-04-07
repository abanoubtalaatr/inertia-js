<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    protected $fillable = ['linkable_type', 'linkable_id', 'type', 'key', 'value'];

    public function linkable()
    {
        return $this->morphTo();
    }
}
