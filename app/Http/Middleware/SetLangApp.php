<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\App;
use Closure;
use Illuminate\Http\Request;

/**
 * Middleware that check if client send current page in header to use it in log system.
 */
class SetLangApp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        $local = $request->header('Accept-Language');

        if ($local != "en" &&  $local != "ar" && $local != "")
        {
            abort(422, 'You must send valid accept language header');
        }

        if (empty($local)) 
        {
            request()->headers->set('Accept-Language', 'en');
        }
        App::setlocale($request->header('Accept-Language'));

        return $next($request);
    }
}
