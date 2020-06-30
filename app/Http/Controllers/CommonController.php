<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RoleAndPermission;

class CommonController extends Controller
{
    // login user can access function
    public function checkPermission($url) {
        $permission = RoleAndPermission::where('user_id', Auth::user()->id)
                        ->where('url', $url)
                        ->get();

        if (!$permission->isEmpty()) {
            return true;
        } else {
            return false;
        }
    }
}
