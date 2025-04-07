<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSectionTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['locale', 'title', 'description'];
}
