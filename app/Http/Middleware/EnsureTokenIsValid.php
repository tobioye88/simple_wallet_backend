<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use App\Utilities\JWT;
use Closure;
use Exception;
use Illuminate\Http\Request;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $bearerToken = $request->header('Authorization');
        if(!$bearerToken){
            return response(ResponseHelper::error(null, "Unauthorized"), 401);
        }
        if ($bearerToken && !$this->isValidToken($bearerToken)) {
            return response(ResponseHelper::error(null, "Unauthorized", 401));
        }
        $request->user = JWT::decode(substr($bearerToken, 7));

        return $next($request);
    }

    public function isValidToken($bearerToken): bool {
        $token = substr($bearerToken, 7);
        return JWT::verify($token, "SUpErSecrete!@#@@@@");
    }
}
