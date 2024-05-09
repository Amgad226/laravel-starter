<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SetUlid
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('ulid',Str::ulid());
        return $next($request);
    }
}
