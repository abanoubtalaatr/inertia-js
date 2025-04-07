<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // dd($request->header('Accept-Language', config('app.locale')));
        $locale = $request->header('Accept-Language', config('app.locale'));

        if ($locale) {
            $locale = explode(',', $locale)[0];
            $locale = in_array($locale, ['ar', 'en']) ? $locale : 'ar';
        } else {
            $locale = 'ar';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
