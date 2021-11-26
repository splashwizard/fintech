<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfProcatcher
{
    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->user()->hasRole('Procatcher#' . $request->user()->business_id) && (!str_contains($request->url(), 'user/profile') &&
            !str_contains($request->url(), 'contacts') && !str_contains($request->url(), 'logout')))
            return redirect('/contacts');
        return $next($request);
    }
}
