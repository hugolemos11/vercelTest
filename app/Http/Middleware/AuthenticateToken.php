<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuthenticateToken
{
    public function handle($request, Closure $next)
    {
        // Read the code from the header
        $code = $request->header('Secret');

        // Order code
        $orderedCode = $this->orderCode($code);

        // Datetime now
        $currentDate = date('Y-m-d');

        $auth = [
            'login' => 'wemakeitmanipulateBasicAuth',
            'password' => '4IZTLdEs8t5eMsgdYxyv' . $orderedCode . $currentDate
        ];

        // Parse login and password from headers
        $b64auth = explode(' ', $request->header('Authorization'));
        if (count($b64auth) < 2) {
            // Handle the error, e.g., return a response or throw an exception
            return response('Forbidden', 403);
        }
        list($login, $password) = explode(':', base64_decode($b64auth[1]));

        // Verify login and password are set and correct
        if ($login === $auth['login'] && $password === $auth['password']) {
            // Access granted...
            return $next($request);
        }

        // Access denied...
        return response('Authentication required.', 401)->header('WWW-Authenticate', 'Basic realm="401"');
    }

    private function orderCode($code)
    {
        $digitsArray = str_split($code);
        rsort($digitsArray);
        return (int) implode('', $digitsArray);
    }
}
