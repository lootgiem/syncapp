<?php

namespace App\Http\Middleware;

use App\Models\Credential;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckStateQueryString
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->filled('state')) {
            $token = Str::urlDecode($request->state);
            $credential = Credential::where('token', hash('sha256', date('Y-m-d').$token))->firstOrFail();

            if ($request->user()->can('update', $credential)) {
                $request->merge(['credential' => $credential]);
                return $next($request);
            }
        }

        return redirect()->route('profil');
    }
}
