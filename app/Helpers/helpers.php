<?php

namespace App\Helpers;

if (! function_exists('getFullImageUrl')) {
    function getFullImageUrl($path)
    {
        return $path ? asset('/storage/'.$path) : null;
    }
}
