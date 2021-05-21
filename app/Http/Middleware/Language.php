<?php

namespace App\Http\Middleware;

use Closure;

use Config;
use Illuminate\Support\Facades\App;

class Language
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = config('app.locale');
        if ($request->session()->has('user.language')) {
            $locale = $request->session()->get('user.language');
        }
        if ($request->get('lang')) {
            $locale = $request->get('lang');
            $request->session()->put('user.language', $locale);
        }
        App::setLocale($locale);


        return $next($request);
    }
}
