<?php
namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ApiAuthenticator
{
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->header('UserId');
        $sessionId = $request->header('SessionId');

        if (!$userId || !$sessionId) {
            return response()->json(["message" => "Missing authentication headers"], 400);
        }

        if (!Session::has('userId') || Session::getId() !== $sessionId || Session::get('userId') !== $userId) {
            return response()->json(["message" => "You are not authorised to perform this action"], 401);
        }

        return $next($request);
    }
}
