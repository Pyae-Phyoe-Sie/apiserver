<?php
 
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\RoleAndPermission;

class VerifyIfStaff
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
        if ( (new \App\Http\Controllers\CommonController)->checkPermission($request->path()) == true)
        {
            return $next($request);
        }
        
        return response()->json(['Message', Auth::user()->name.' do not access to this module.'], 403);
    }
}