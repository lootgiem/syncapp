<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Token;
use Lcobucci\JWT\Parser;

class AuthApi
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request = $this->addUserToRequest($request);
        return $next($request);
    }

    private function addUserToRequest(Request $request): Request
    {
        $user = $this->getUserFromApiToken($request);
        $request->merge(['user' => $user]);

        $request->setUserResolver(function () use ($user)
        {
            return $user;
        });

        Auth::setUser($user);

        return $request;
    }

    private function getUserFromApiToken(Request $request)
    {
        $bearerToken = $request->bearerToken();
        $tokenId = (new Parser())->parse($bearerToken)->getClaim('jti');
        return Token::find($tokenId)->client->user;
    }
}
