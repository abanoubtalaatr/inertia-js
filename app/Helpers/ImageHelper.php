<?php

namespace App\Helpers;

class ImageHelper
{
    public static function getFullImageUrl($path)
    {
        return $path ? asset('storage/'.$path) : null;
    }
}
