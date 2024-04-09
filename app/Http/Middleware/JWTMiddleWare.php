<?php

namespace App\Http\Middleware;

use Tymon\JWTAuth\Facades\JWTAuth;
use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenNotFoundException;
use Illuminate\Http\Response;

class JWTMiddleWare
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            // Handle TokenExpiredException
            return response()->json(['error' => 'Token has expired'], Response::HTTP_FORBIDDEN);
        } catch (TokenInvalidException $e) {
            // Handle TokenInvalidException
            return response()->json(['error' => 'Invalid token'], Response::HTTP_FORBIDDEN);
        } catch (JWTException $e) {
            // Handle other JWT exceptions
            return response()->json(['error' => 'Token not provided'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
