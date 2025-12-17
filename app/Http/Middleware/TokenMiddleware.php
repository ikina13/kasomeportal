<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\app_user as AppUser;

class TokenMiddleware
{
    public function handle($request, Closure $next)
    {
        // Validate token
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if the authorization token is present
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Unauthorized. Token is missing.'], 401);
        }

        // Extract content from the token
        $user = Auth::user();
        $userPhone = $user->phone;
        $userId = AppUser::where('phone',$userPhone)->value('id');

        // You can extract any other information from the user or token as needed

        // Pass the user ID to the request for later use
        $request->merge(['user_id' => $userId]);

        return $next($request);
    }
}
