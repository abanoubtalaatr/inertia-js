<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutUsItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'about_us_section_id',
        'title_en', 'description_en',
        'title_ar', 'description_ar',
        'title_fr', 'description_fr',
        'title_tl', 'description_tl',
        'title_ur', 'description_ur',
        'order'
    ];

    public function section()
    {
        return $this->belongsTo(AboutUsSection::class);
    }
}
