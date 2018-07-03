<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;


class RolesAdminAuthenticated
{
    public function handle($request, Closure $next, $guard = null)
    {
        $user = Auth::user();
        $roleType = \Config::get('constants.ROLE_TYPE');

        if ($user->type == $roleType['WEB'] && $user->is_root_admin == true) {
            // Admin
            return $next($request);
        } else if ($user->type == $roleType['IOS']) {
            // IOS
            return response()->json([
                'error' => true,
                'message' => "You don't have permission",
                'data' => "You login for mobile"
            ], 403);
        }

        return response()->json(['error' => true, 'message' => "You don't have permission"], 403);
    }
}
