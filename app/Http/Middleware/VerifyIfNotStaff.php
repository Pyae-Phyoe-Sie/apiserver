<?php
 
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Support\Facades\Auth;

class VerifyIfNotStaff
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
        if ( Auth::check() && Auth::user()->role != 1 )
        {
            return $next($request);
        }

        return response()->json(['Message', Auth::user()->name.' do not access to this module.'], 403);
    }
}